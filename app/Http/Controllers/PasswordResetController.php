<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Managers\VerificationManager;

/**
 * @see PasswordResetControllerTest
 */
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

        $verificationManager = new VerificationManager($email);
        $verificationManager->sendVerification(true);

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
            $verificationManager = new VerificationManager($request->session()->get('email'));

            if ($verificationManager->verify($token)) {
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

            $verificationManager = new VerificationManager($email);
            $verificationManager->sendVerification();

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

            if (strlen($request->get('password')) < static::MINIMUM_PASSWORD_LENGTH) {
                return response()->json(['message' => 'Invalid password'], 400);
            }

            if ($request->get('password') !== $request->get('password_confirmation')) {
                return response()->json(['message' => 'The password does not match the confirmation'], 400);
            }
            
            $user = User::fromEmail($request->session()->get('email'));
            $user->setPassword($request->get('password'));
            $request->session()->flush();
            return response(null, 200);
        }
        
        return response(null, 403);
    }
}
