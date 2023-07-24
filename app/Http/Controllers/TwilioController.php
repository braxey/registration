<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\TwiML\MessagingResponse;

class TwilioController extends Controller
{
    public function handleIncomingSMS(Request $request)
    {
        // Get the incoming SMS content
        $incomingMessage = $request->input('Body');

        // Customize your reply based on the incoming message (you can implement your logic here)
        $replyMessage = "Thank you for your message! This number is automated and cannot respond to individual inquiries. Have a great day!";

        // Create a TwiML response with the customized reply message
        $response = new MessagingResponse();
        $response->message($replyMessage);

        // Return the TwiML response
        return response($response)->header('Content-Type', 'text/xml');
    }
}
