<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use App\Models\User;
use App\Models\PhoneVerification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    protected $client;

    public function __construct()
    {
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");

        $this->client = new Client($account_sid, $auth_token);
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

        $this->sendNewToken($phone);
        return view('auth.forgot-pass-verify-phone',
            [
                'masked_phone' => $this->maskPhone($phone)
            ]
        );
    }

    public function verify(Request $request)
    {
        try {
            // Get the cleaned token
            $token = $this->cleanToken($request->get('token')); 
            $phone = session('phone_number');
            
            // Check if the token is valid
            if ($this->isValidPhoneNumber($phone) && $this->isValidToken($token)) {
                $user = User::where('phone_number', $phone)->first();
                if (PhoneVerification::getMostRecentToken($user->id) === $token) {
                    // Delete phone_verifications rows with the user_id
                    PhoneVerification::where('user_id', $user->id)->delete();
                    session(['reset_verified' => 1]);
                    return response(200);
                } else {
                    return response(['message' => 'Wrong token'], 400);
                }
            } else {
                if (!$this->isValidPhoneNumber($phone)) {
                    return response(['message' => 'Invalid phone number'], 400);
                }
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
            $phone = session('phone_number');
            
            if ($this->isValidPhoneNumber($phone)) {
                $user = User::where('phone_number', $phone)->first();
                $token = PhoneVerification::getMostRecentToken($user->id);

                // Send and log token
                $this->sendToken($user, $token);
                return view(
                    'auth.forgot-pass-verify-phone',
                    [
                        'masked_phone' => $this->maskPhone($phone)
                    ]
                );
            } else {
                return response(['message' => 'Invalid phone number'], 400);
            }
        } catch (Exception $e) {
            return response(['message' => 'Something went wrong'], 500);
        }
    }

    public function getResetPasswordForm()
    {
        if ($this->isValidPhoneNumber(session('phone_number')) && session('reset_verified')) {
            return view('auth.reset-password');
        }
        abort(404);
    }

    public function updatePassword(Request $request)
    {
        if ($this->isValidPhoneNumber(session('phone_number')) && session('reset_verified')) {
            $password = $request->get('password');
            $password_confirmation = $request->get('password_confirmation');

            $input = [
                'password' => $password,
            ];
            $validator = Validator::make($input, [
                'password' => 'min:6'
            ]);

            if ($validator->passes()) {
                if ($password !== $password_confirmation) {
                    return response(['message' => 'The password does not match the confirmation'], 400);
                } else {
                    $user = User::where('phone_number', session('phone_number'))->first();
                    $user->forceFill([
                        'password' => Hash::make($input['password']),
                    ])->save();
                    session([
                        'phone_number' => '',
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
        $this->sendToken($user, $token);
    }

    private function sendToken($user, $token)
    {
        $maxSendAttempts     = getenv('MAX_SEND_ATTEMPTS');
        $secondsBetweenSends = getenv('SECONDS_BETWEEN_SENDS');
        for ($attempt = 1; $attempt <= $maxSendAttempts; $attempt++) {
            $message = $this->client->messages->create(
                $user->phone_number,
                [
                    'from' => getenv('TWILIO_PHONE_NUMBER'),
                    'body' => 'Your verification token is: ' . $token,
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

    private function maskPhone($phone)
    {
        return '(***) *** - **' . substr($phone, strlen($phone) - 2);
    }
}
