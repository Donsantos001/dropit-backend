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
    public function createOTP(User $user)
    {
        $otp = 111111;
        // $otp = rand(100000, 999999);

        // Send otp through remote service
        try {
            Mail::to('santosdboss@gmail.com')->send(new VerificationMail($otp));
        } catch (Exception $e) {
            response()->json([
                'message' => 'Unable to send otp, try again'
            ], 401);
        }

        DB::table('email_verification_tokens')->updateOrInsert(
            ['email' => $user->email],
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
            'otp' => 'required|numeric:6',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $record = DB::table('email_verification_tokens')->where('email', $user->email)->where('otp', $request->otp);
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
        $user->email_verified_at = now();
        $user->save();

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
            'message' => 'Email verified successfully',
            'verified' => true,
        ]);
    }
}
