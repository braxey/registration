<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Auth;
use App\Models\PhoneVerification;

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
        $this->sendNewToken();
        $user = Auth::user();
        return view('auth.verify-phone', ['masked_phone' => $this->maskPhone($user->phone_number)]);
    }

    public function verify(Request $request)
    {
        try {
            // Get the cleaned token
            $token = $this->cleanToken($request->get('token'));
            
            // Check if the token is valid
            if ($this->validToken($token)) {
                $user = Auth::user();
                if (PhoneVerification::getMostRecentToken($user->id) === $token) {
                    $user->verifyPhone();
                    // Delete phone_verifications rows with the user_id
                    PhoneVerification::where('user_id', $user->id)->delete();
                    return response(200);
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
            $this->sendToken($user, $token);
            return view('auth.verify-phone', ['masked_phone' => $this->maskPhone($user->phone_number)]);
        } catch (Exception $e) {
            return response(['message' => 'Invalid token'], 400);
        }
    }

    private function sendNewToken()
    {
        // Generate token
        $token = (string) mt_rand(1000000, 9999999);

        // Send token
        $user = Auth::user();
        $this->sendToken($user, $token);
    }

    private function sendToken($user, $token)
    {
        $this->client->messages->create(
            $user->phone_number,
            [
                'from' => getenv("TWILIO_PHONE_NUMBER"),
                'body' => "Your verification token is: " . $token,
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
    }

    private function validToken($token)
    {
        return ctype_digit($token) && strlen($token) == 7;
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
