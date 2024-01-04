<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use App\Constants\EmailTypes;
use App\Models\User;
use App\Models\PhoneVerification;
use App\Models\QueuedEmail;

class PasswordResetController extends Controller
{
    private const MINIMUM_PASSWORD_LENGTH = 8;

    public function getForgotPasswordPage()
    {
        return view('auth.forgot-password');
    }

    public function getVerifyEmailPage(Request $request)
    {
        if ($request->session()->missing('email')) {
            return redirect()->route('get-forgot-password');
        }

        $email = $request->session()->get('email');
        $request->session()->put('reset-verified', 0);

        $this->emailNewToken($email);
        return view('auth.forgot-pass-verify', [
            'email' => $email
        ]);
    }

    public function getResetPasswordPage(Request $request)
    {
        if ($request->session()->get('reset-verified') === 1) {
            return view('auth.reset-password');
        }
        
        if ($request->session()->has('email')) {
            return redirect()->route('forgot-password.get-verify-email');
        }

        return redirect()->route('get-forgot-password');
    }

    public function verifyEmail(Request $request)
    {
        $email = trim($request->get('email'));
        $user = User::fromEmail($email);
        if ($user === null) {
            return response()->json(['message' => 'No user found'], 400);
        }
        
        $request->session()->put('email', $email);
        return response(null, 200);
    }

    public function verifyToken(Request $request)
    {
        try {
            $token = trim($request->get('token'));            
            $verification = PhoneVerification::fromUserEmail($request->session()->get('email'));

            if ($verification->isValidToken($token)) {
                $verification->verify();
                $request->session()->put('reset-verified', 1);
                return response(null, 200);
            }

            return response()->json(['message' => 'Wrong token'], 400);
        } catch (Exception $e) {
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function resendToken(Request $request)
    {
        try {
            $email = $request->session()->get('email');
            $resetVerified = $request->session()->get('reset-verified');
            if ($email === null || $resetVerified !== 0) {
                return response(null, 401);
            }

            $token = PhoneVerification::fromUserEmail($email)->getToken();
            $this->emailToken($email, $token);

            return view('auth.forgot-pass-verify', [
                'email' => $email
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        if ($request->session()->get('reset-verified') === 1) {
            $payload = $request->all();

            if (strlen($payload['password']) < static::MINIMUM_PASSWORD_LENGTH) {
                return response()->json(['message' => 'Invalid password'], 400);
            }

            if ($payload['password'] !== $payload['password_confirmation']) {
                return response()->json(['message' => 'The password does not match the confirmation'], 400);
            }
            
            $user = User::fromEmail($request->session()->get('email'));
            $user->setPassword($payload['password']);
            $request->session()->flush();
            return response(null, 200);
        }
        
        return response(null, 403);
    }

    private function emailNewToken(string $email)
    {
        $token = generateSecureNumericToken();
        $this->emailToken($email, $token);
    }

    private function emailToken(string $email, string $token)
    {
        $user = User::fromEmail($email);
        $payload = ['token' => $token];
        QueuedEmail::queue($email, EmailTypes::VERIFICATION, $payload);
        PhoneVerification::logTokenSend($user, $token);

        // kick off sending queued emails so verifications get sent as soon as they can
        Artisan::call('app:send-queued-emails');
    }
}
