<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\OTPMail;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|unique:users',
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $otp = rand(100000, 999999);
        $user = User::create([
            'phone_number' => $request->phone_number,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'otp_code' => $otp
        ]);

        // Send OTP using Mailable class
        try {
            Mail::to($user->email)->send(new OTPMail($user->username, $otp));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send OTP. Try again.'], 500);
        }

        return response()->json([
            'message' => 'User registered successfully. Check email for OTP.',
            'user_id' => $user->id
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'otp_code' => 'required'
        ]);

        $user = User::find($request->user_id);

        if ($user->otp_code == $request->otp_code) {
            $token = Str::random(60);
            $user->update(['is_verified' => true, 'otp_code' => null, 'token' => $token]);

            return response()->json([
                'message' => 'Account verified successfully.',
                'token' => $token
            ]);
        }

        return response()->json(['message' => 'Invalid OTP'], 400);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->is_verified) {
            return response()->json(['message' => 'Account not verified. Please verify your email.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

}
