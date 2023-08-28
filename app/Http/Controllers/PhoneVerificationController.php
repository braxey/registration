<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PhoneVerification;
use libphonenumber\PhoneNumberUtil;

class PhoneVerificationController extends Controller
{
    protected $client;


    public function __construct()
    {
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");

        $this->client = new Client($account_sid, $auth_token);
    }


    public function getVerifyForm()
    {
        $validPhone = $this->sendNewToken();
        $user = Auth::user();
        return view('auth.verify-phone', [
            'masked_phone' => $this->maskPhone($user->phone_number),
            'valid_phone'  => $validPhone
        ]);
    }

    public function verify(Request $request)
    {
        try {
            // Get the cleaned token
            $token = $this->cleanToken($request->get('token'));
            $valid = $request->get('valid');

            if(!$valid) {
                return response(['message' => 'Invalid phone'], 400);
            }
            
            // Check if the token is valid
            if ($this->validToken($token)) {
                $user = Auth::user();
                if (PhoneVerification::getMostRecentToken($user->id) === $token) {
                    $user->verifyPhone();
                    // Delete phone_verifications rows with the user_id
                    PhoneVerification::where('user_id', $user->id)->delete();
                    if (session('ref')) {
                        $ref = session('ref');
                        session(['ref' => null]);
                        return response(['ref' => $ref], 200);
                    }
                    return response(['ref' => null], 200);
                } else {
                    return response(['message' => 'Wrong token'], 400);
                }
            } else {
                return response(['message' => 'Invalid token'], 400);
            }
        } catch (Exception $e) {
            return response(['message' => 'Invalid token'], 400);
        }
    }

    public function resend()
    {
        try {
            // Get token to resend
            $user = Auth::user();
            $token = PhoneVerification::getMostRecentToken($user->id);

            // Send and log token
            if (!$this->sendToken($user, $token)) {
                $validPhone = false;
            } else {
                $validPhone = true;
            }
        } catch (Exception $e) {
            $validPhone = false;
        }
        return view('auth.verify-phone', [
            'masked_phone' => $this->maskPhone($user->phone_number),
            'valid_phone'  => $validPhone
        ]);
    }

    public function getChangePhoneForm()
    {
        return view('auth.change-phone');
    }

    public function changePhone(Request $request)
    {
        $phone = $request->get('phone_number');
        if ($this->isValidPhoneNumber($phone)) {
            if (!$this->userWithNumberExists($phone)) {
                $user = Auth::user();
                $user->phone_number = $phone;
                $user->save();
                return response(200);
            }
            return response(['message' => 'User exists'], 400);
        }
        return response(['message' => 'Invalid number'], 400);
    }

    private function sendNewToken()
    {
        // Generate token
        $token = (string) mt_rand(1000000, 9999999);

        // Send token
        $user = Auth::user();
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

            $this->client->messages->create(
                $user->phone_number,
                [
                    'from' => getenv("TWILIO_PHONE_NUMBER"),
                    'body' => "Your WTB Registration verification code is: " . $token,
                ]
            );
    
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

    private function validToken($token)
    {
        return ctype_digit($token) && strlen($token) == 7;
    }
    
    private function isValidPhoneNumber($number)
    {
        return ctype_digit($number) && strlen($number) == 10;
    }

    private function userWithNumberExists($phone)
    {
        $currPhone = Auth::user()->phone_number;
        return !empty(User::where('phone_number', $phone)->first()) && !($phone == $currPhone);
    }

    private function cleanToken($token)
    {
        return preg_replace('/\s+/', '', $token);
    }

    private function maskPhone($phone)
    {
        return '(***) *** - **' . substr($phone, strlen($phone) - 2);
    }
}
