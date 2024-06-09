<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;
use Tests\TestCase;
use App\Models\User;
use App\Models\PhoneVerification;

/**
 * @see PasswordResetController
 */
class PasswordResetControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->user = User::factory()->create();
    }

    /* ========== FORGOT PASSWORD PAGE ========== */

    public function testForgotPasswordPageCanBeReached()
    {
        $this->get(route('get-forgot-password'))
            ->assertSuccessful()
            ->assertViewIs('authentication.forgot-password');
    }

    public function testEmailCanBeInsertedIntoSession()
    {
        $this->submitEmail($this->user->getEmail())->assertSuccessful();
        $this->assertTrue(session('email') === $this->user->getEmail());
    }

    public function testErrorThrownIfNoUserWithEmailExists()
    {
        $this->submitEmail($this->faker->email)->assertBadRequest()->assertJson(['message' => 'No user found']);
        $this->assertNull(session('email'));
    }

    /* ========== VERIFY TOKEN ========== */

    public function testTokenVerificationPageCanBeReached()
    {
        $email = $this->user->getEmail();
        $this->submitEmail($email);
        $this->get(route('forgot-password.get-verify-email'))
            ->assertSuccessful()
            ->assertViewIs('authentication.forgot-password-verify')
            ->assertViewHas('email', $email)
            ->assertViewHas('rate_limit', false);

        $this->assertTrue(session('reset-verified') === 0);
        Mail::assertSent(VerificationEmail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
        $this->assertDatabaseHas('phone_verifications', [
            'user_id' => $this->user->getId()
        ]);
    }

    public function testTokenNotSentIfRateLimitReached()
    {
        $email = $this->user->getEmail();
        $this->submitEmail($email);
        // seed verification sends to breach rate limit
        PhoneVerification::factory()->withUser($this->user)->count(config('mail.verification-limit'))->create();

        $this->get(route('forgot-password.get-verify-email'))
            ->assertSuccessful()
            ->assertViewIs('authentication.forgot-password-verify')
            ->assertViewHas('email', $email)
            ->assertViewHas('rate_limit', true);

        $this->assertTrue(session('reset-verified') === 0);
        Mail::assertNotSent(VerificationEmail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    public function testCannotReachTokenVerificationPageWithoutCheckedEmail()
    {
        $email = $this->user->getEmail();
        $this->get(route('forgot-password.get-verify-email'))
            ->assertRedirect(route('get-forgot-password'));

        $this->assertNull(session('email'));
        $this->assertNull(session('reset-verified'));
        Mail::assertNotSent(VerificationEmail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    public function testTokenCanBeVerified()
    {
        $email = $this->user->getEmail();
        $this->submitEmail($email);
        $this->get(route('forgot-password.get-verify-email'));

        $token = PhoneVerification::fromUserEmail($email)->getToken();

        $this->submitToken($token)->assertSuccessful();
        $this->assertTrue(session('reset-verified') === 1);
        $this->assertNull(PhoneVerification::fromUserEmail($email));
    }

    public function testTokenVerificationFailsWithWrongToken()
    {
        $email = $this->user->getEmail();
        $this->submitEmail($email);
        $this->get(route('forgot-password.get-verify-email'));

        $this->submitToken('wrong token')->assertBadRequest()->assertJson(['message' => 'Wrong token']);
        $this->assertTrue(session('reset-verified') === 0);
        $this->assertNotNull(PhoneVerification::fromUserEmail($email));
    }

    /* ========== RESEND TOKEN ========== */

    public function testTokenCanBeResent()
    {
        $email = $this->user->getEmail();
        $this->submitEmail($email);
        $this->get(route('forgot-password.get-verify-email'));
        $this->resendToken()->assertSuccessful()->assertViewIs('authentication.forgot-password-verify')
            ->assertViewHas('email', $email)->assertViewHas('rate_limit', false);
        $this->assertCount(2, PhoneVerification::where('user_id', $this->user->getId())->get());

        Mail::assertSent(VerificationEmail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    public function testOnlyResendWhenOnTokenVerifyStep()
    {
        // email not in session
        $this->resendToken()->assertUnauthorized();
        $this->assertNull(PhoneVerification::fromUserEmail($this->user->getEmail()));

        // already verified
        $this->submitEmail($this->user->getEmail());
        $this->get(route('forgot-password.get-verify-email'));
        $token = PhoneVerification::fromUserEmail($this->user->getEmail())->getToken();
        $this->submitToken($token);

        $this->resendToken()->assertUnauthorized();
        $this->assertNull(PhoneVerification::fromUserEmail($this->user->getEmail()));
    }

    public function testCannotResendIfRateLimited()
    {
        $email = $this->user->getEmail();
        $this->submitEmail($email);
        // send verification sends to breach rate limit
        PhoneVerification::factory()->withUser($this->user)->count(config('mail.verification-limit'))->create();
        $this->get(route('forgot-password.get-verify-email'));

        $this->resendToken()->assertSuccessful()->assertViewIs('authentication.forgot-password-verify')
            ->assertViewHas('email', $email)->assertViewHas('rate_limit', true);

        Mail::assertNotSent(VerificationEmail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    /* ========== RESET PASSWORD ========== */

    public function testResetPasswordPageCanBeReached()
    {
        $this->submitEmail($this->user->getEmail());
        $this->get(route('forgot-password.get-verify-email'));
        $token = PhoneVerification::fromUserEmail($this->user->getEmail())->getToken();
        $this->submitToken($token);

        $this->get(route('forgot-password.get-reset'))->assertSuccessful()->assertViewIs('authentication.reset-password');
    }

    public function testResetPasswordPageCannotBeReachedIfEmailNotVerified()
    {
        // email not checked
        $this->get(route('forgot-password.get-reset'))->assertRedirect(route('get-forgot-password'));

        // email not verified
        $this->submitEmail($this->user->getEmail());
        $this->get(route('forgot-password.get-reset'))->assertRedirect(route('forgot-password.get-verify-email'));
    }

    public function testPasswordCanBeReset()
    {
        $this->submitEmail($this->user->getEmail());
        $this->get(route('forgot-password.get-verify-email'));
        $token = PhoneVerification::fromUserEmail($this->user->getEmail())->getToken();
        $this->submitToken($token);

        $password = 'valid-password';
        $this->submitPasswordUpdate($password, $password)->assertSuccessful();

        $this->assertNull(session('email'));
        $this->assertNull(session('reset-verified'));
    }

    public function testPasswordCannotBeResetIfEmailNotVerified()
    {
        $password = 'valid-password';

        // not checked
        $this->submitPasswordUpdate($password, $password)->assertForbidden();

        // checked, but not verified
        $this->submitEmail($this->user->getEmail());
        $this->submitPasswordUpdate($password, $password)->assertForbidden();
        $this->assertNotNull(session('email'));
    }

    public function testPasswordValidation()
    {
        $this->submitEmail($this->user->getEmail());
        $this->get(route('forgot-password.get-verify-email'));
        $token = PhoneVerification::fromUserEmail($this->user->getEmail())->getToken();
        $this->submitToken($token);

        // password too short
        $password = 'short';
        $this->submitPasswordUpdate($password, $password)->assertBadRequest()
            ->assertJson(['message' => 'Invalid password']);

        $this->assertNotNull(session('email'));
        $this->assertNotNull(session('reset-verified'));

        // password doesn't match confirmation
        $password = 'valid password';
        $confirmation = $password . 'a';
        $this->submitPasswordUpdate($password, $confirmation)->assertBadRequest()
            ->assertJson(['message' => 'The password does not match the confirmation']);

        $this->assertNotNull(session('email'));
        $this->assertNotNull(session('reset-verified'));
    }

    /* ========== HELPER FUNCTIONS ========== */

    private function submitEmail(string $email)
    {
        return $this->post(route('forgot-password.check-email', [
            'email' => $email
        ]));
    }

    private function submitToken(string $token)
    {
        return $this->post(route('forgot-password.verify-token'), [
            'token' => $token
        ]);
    }

    private function resendToken()
    {
        return $this->post(route('forgot-password.resend-token'));
    }

    private function submitPasswordUpdate(string $password, string $passwordConfirmation)
    {
        return $this->post(route('forgot-password.update-password'), [
            'password' => $password,
            'password_confirmation' => $passwordConfirmation
        ]);
    }
}
