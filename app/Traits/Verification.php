<?php

namespace App\Traits;

use App\Mail\VerificationMail;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

trait Verification
{
    /**
     * Resend OTP
     *
     * @param  [string] email
     * @param  [string] token
     */
    public function createOTP(Request $request)
    {
        $otp = rand(100000, 999999);

        // Send otp through remote service
        try {
            Mail::to('santosdboss@gmail.com')->send(new VerificationMail($otp));
        } catch (Exception $e) {
            response()->json([
                'message' => 'Unable to send otp, try again'
            ], 401);
        }

        DB::table('email_verification_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['otp' => $otp, 'created_at' => now()]
        );
    }

    /**
     * Verify OTP
     *
     * @param  [string] email
     * @param  [string] token
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'otp' => 'required|numeric:6',
        ]);

        $record = DB::table('email_verification_tokens')->where('email', $request->email)->where('otp', $request->otp);
        $verification = $record->first();
        if (!$verification) {
            return response()->json([
                'error' => 'Invalid OTP',
            ], 401);
        }

        if (Carbon::parse($verification->created_at)->diffInMinutes(now()) > 5) {
            return response()->json([
                'error' => 'Otp has expired',
            ], 401);
        }

        $record->delete();
        $user = User::where('email', $request->email)->first();
        $user->email_verified_at = now();
        $user->save();
        $tokenResult = $user->createToken('Personal Access Token', ['*'], now()->addDays(2));
        $token = $tokenResult->plainTextToken;

        // Send success through remote service
        try {
            Mail::to('santosdboss@gmail.com')->send(new VerificationMail(null));
        } catch (Exception $e) {
            response()->json([
                'message' => 'Email verified',
                'error' => 'Unable to send email'
            ]);
        }

        return response()->json([
            'message' => 'Successfully verified user!',
            'accessToken' => $token,
            'verified' => true,
        ]);
    }
}
