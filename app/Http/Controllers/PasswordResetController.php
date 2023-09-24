<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use App\Models\User;
use App\Models\PhoneVerification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use libphonenumber\PhoneNumberUtil;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;

class PasswordResetController extends Controller
{
    protected $client;

    public function __construct()
    {
        // $account_sid = getenv("TWILIO_SID");
        // $auth_token = getenv("TWILIO_AUTH_TOKEN");

        // $this->client = new Client($account_sid, $auth_token);
    }

    public function getForgotPasswordPage()
    {
        return view('auth.forgot-password');
    }

    public function verifyNumber(Request $request)
    {
        $phone = $request->get('phone_number');
        if ($this->isValidPhoneNumber($phone)) {
            if ($this->userWithNumberExists($phone)) {
                return response(200);
            }
            return response(['message' => 'No user found'], 400);
        }
        return response(['message' => 'Invalid number'], 400);
    }

    public function verifyEmail(Request $request)
    {
        $email = $request->get('email');
        if ($this->userWithEmailExists($email)) {
            return response(200);
        }
        return response(['message' => 'No user found'], 400);
    }

    public function getNumberVerifyForm(Request $request)
    {
        $phone = $request->get('phone_number');
        if (!$this->isValidPhoneNumber($phone)) {
            return response(['message' => 'Invalid phone number'], 400);
        }

        session(
            [
                'phone_number'   => $phone,
                'reset_verified' => 0
            ]
        );

        $validPhone = $this->sendNewToken($phone);
        return view('auth.forgot-pass-verify-phone',
            [
                'masked_phone' => $this->maskPhone($phone),
                'valid_phone'  => $validPhone
            ]
        );
    }

    public function getEmailVerifyForm(Request $request)
    {
        $email = $request->get('email');
        session(
            [
                'email'          => $email,
                'reset_verified' => 0
            ]
        );

        $this->emailNewToken($email);
        return view('auth.forgot-pass-verify-phone',
            [
                // 'masked_phone' => $this->maskPhone($phone),
                'email'        => $email
            ]
        );
    }

    public function verify(Request $request)
    {
        try {
            // Get the cleaned token
            $token = $this->cleanToken($request->get('token')); 
            // $valid = $request->get('valid');

            // if(!$valid) {
            //     return response(['message' => 'Invalid phone'], 400);
            // }
            // $phone = session('phone_number');
            $email = session('email');
            
            // Check if the token is valid
            if ($this->isValidToken($token)) {
                $user = User::where('email', $email)->first();
                if (PhoneVerification::getMostRecentToken($user->id) === $token) {
                    // Delete phone_verifications rows with the user_id
                    PhoneVerification::where('user_id', $user->id)->delete();
                    session(['reset_verified' => 1]);
                    return response(200);
                } else {
                    return response(['message' => 'Wrong token'], 400);
                }
            } else {
                // if (!$this->isValidPhoneNumber($phone)) {
                //     return response(['message' => 'Invalid phone number'], 400);
                // }
                return response(['message' => 'Invalid token'], 400);
            }
        } catch (Exception $e) {
            return response(['message' => 'Something went wrong'], 500);
        }
    }

    public function resend(Request $request)
    {
        try {
            // Get token to resend
            // $phone = session('phone_number');
            $email = session('email');
            
            // if ($this->isValidPhoneNumber($phone)) {
                $user = User::where('email', $email)->first();
                $token = PhoneVerification::getMostRecentToken($user->id);
                dump($token);

                // Send and log token
                $this->emailToken($user, $token);
                return view(
                    'auth.forgot-pass-verify-phone',
                    [
                        // 'masked_phone' => $this->maskPhone($phone),
                        // 'valid_phone'  => $validPhone
                        'email' => $email
                    ]
                );
            // } else {
            //     return response(['message' => 'Invalid phone number'], 400);
            // }
        } catch (Exception $e) {
            return response(['message' => 'Something went wrong'], 500);
        }
    }

    public function getResetPasswordForm()
    {
        if (session('reset_verified')) {
            return view('auth.reset-password');
        }
        abort(404);
    }

    public function updatePassword(Request $request)
    {
        if (session('reset_verified')) {
            $password = $request->get('password');
            $password_confirmation = $request->get('password_confirmation');

            $input = [
                'password' => $password,
            ];
            $validator = Validator::make($input, [
                'password' => 'min:8'
            ]);

            if ($validator->passes()) {
                if ($password !== $password_confirmation) {
                    return response(['message' => 'The password does not match the confirmation'], 400);
                } else {
                    $user = User::where('email', session('email'))->first();
                    $user->forceFill([
                        'password' => Hash::make($input['password']),
                    ])->save();
                    session([
                        'email' => '',
                        'reset_verified' => 0
                    ]);
                    return response(200);
                }
            }
            return response(['message' => 'Invalid password'], 400);
        }
        abort(404);
    }

    private function sendNewToken($phone)
    {
        // Generate token
        $token = (string) mt_rand(1000000, 9999999);

        // Send token
        $user = User::where('phone_number', $phone)->first();
        return $this->sendToken($user, $token);
    }

    private function sendToken($user, $token)
    {
        // Validate phone number using libphonenumber
        $phoneNumberUtil = PhoneNumberUtil::getInstance();

        try {
            $parsedPhoneNumber = $phoneNumberUtil->parse($user->phone_number, 'US');
            if (!$phoneNumberUtil->isValidNumber($parsedPhoneNumber)) {
                throw new \Exception('Invalid phone number');
            }

            $maxSendAttempts     = getenv('MAX_SEND_ATTEMPTS');
            $secondsBetweenSends = getenv('SECONDS_BETWEEN_SENDS');
            for ($attempt = 1; $attempt <= $maxSendAttempts; $attempt++) {
                $message = $this->client->messages->create(
                    $user->phone_number,
                    [
                        'from' => getenv('TWILIO_PHONE_NUMBER'),
                        'body' => 'Your WTB Registration verification code is: ' . $token,
                    ]
                );

                // Check if the message was successfully sent
                if ($message->sid) {
                    break; // Exit the loop if the message was sent successfully
                } else {
                    sleep($secondsBetweenSends);
                }
            }

            // Log token
            $phoneVerification = PhoneVerification::create([
                'token' => $token,
                'time_sent' => now(),
                'user_id' => $user->id,
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function emailNewToken($email)
    {
        // Generate token
        $token = (string) mt_rand(1000000, 9999999);

        // Send token
        $user = User::where('email', $email)->first();
        return $this->emailToken($user, $token);
    }

    private function emailToken($user, $token)
    {
        try {
            Mail::to($user->email)->send(new VerificationEmail($token));

            // Log token
            $phoneVerification = PhoneVerification::create([
                'token' => $token,
                'time_sent' => now(),
                'user_id' => $user->id,
            ]);

            return true;
        } catch (\Exception $e) {
            dump($e);
            return false;
        }
    }

    private function isValidToken($token)
    {
        return ctype_digit($token) && strlen($token) == 7;
    }
    

    private function cleanToken($token)
    {
        return preg_replace('/\s+/', '', $token);
    }

    private function isValidPhoneNumber($number)
    {
        return ctype_digit($number) && strlen($number) == 10;
    }

    private function userWithNumberExists($phone)
    {
        return !empty(User::where('phone_number', $phone)->first());
    }

    private function userWithEmailExists($email)
    {
        return !empty(User::where('email', $email)->first());
    }

    private function maskPhone($phone)
    {
        return '(***) *** - **' . substr($phone, strlen($phone) - 2);
    }
}
