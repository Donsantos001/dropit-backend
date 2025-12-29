<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class PasswordResetController extends Controller
{
    public function sendResetOTP(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return ResponseBuilder::asError(422)
                ->withMessage('User not found')
                ->build();
        }

        $otp = (string) random_int(100000, 999999);

        try {
            Mail::to($user->email)->send(new PasswordResetMail($otp));
        } catch (\Throwable $e) {
            // If mail fails, still proceed with OTP creation
        }

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => $otp, 'created_at' => now()]
        );

        return ResponseBuilder::asSuccess()
            ->withData(['info' => 'OTP sent and expires in 5 minutes'])
            ->withMessage('Password reset OTP sent successfully.')
            ->build();
    }

    public function reset(ResetPasswordRequest $request)
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token);

        $verification = $record->first();
        if (!$verification) {
            return ResponseBuilder::asError(401)
                ->withMessage('Invalid OTP')
                ->build();
        }

        if (Carbon::parse($verification->created_at)->diffInMinutes(now()) > 5) {
            return ResponseBuilder::asError(401)
                ->withMessage('OTP has expired')
                ->build();
        }

        $record->delete();

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return ResponseBuilder::asError(422)
                ->withMessage('User not found')
                ->build();
        }

        $user->password = Hash::make($request->password);
        $user->save();

        $tokenResult = $user->createToken($request->get('device_name', 'accessToken'), ['*'], now()->addDays(2));
        $token = $tokenResult->plainTextToken;

        return ResponseBuilder::asSuccess()
            ->withData([
                'user' => $user,
                'accessToken' => $token,
                'token_type' => 'Bearer',
            ])
            ->withMessage('Password reset successfully.')
            ->build();
    }
}
