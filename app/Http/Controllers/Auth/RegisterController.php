<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Traits\Verification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{

    use Verification;

    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @return [string] message
     */
    public function register(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|string|unique:users',
            'phone_no' => 'required|string|unique:users',
            'password' => 'required|string',
            'referred_by' => 'string|nullable',
        ]);

        $referral_code = $this->generateReferralCode();
        $referredby = null;
        if ($request->referred_by) {
            $referredby = User::where('referral_code', $request->referred_by)->first()->id;
        }

        $user = new User([
            'firstname'  => $request->firstname,
            'lastname'  => $request->lastname,
            'email' => $request->email,
            'phone_no' => $request->phone_no,
            'referred_by' => $referredby,
            'referral_code' => $referral_code,
            'password' => bcrypt($request->password),
        ]);


        if ($user->save()) {
            $tokenResult = $user->createToken('Personal Access Token', ['*'], now()->addDays(2));
            $token = $tokenResult->plainTextToken;

            return response()->json([
                'message' => 'User created successfully',
                'accessToken' => $token,
                'verified' => false,
            ], 201);
        }

        return response()->json(['error' => 'Provide proper details']);
    }

    /**
     * Resend OTP
     *
     * @param  [string] email
     * @param  [string] token
     */
    public function sendOTP(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if ($user->email_verified_at) {
            return response()->json(['error' => 'Email already verified'], 422);
        }
        $this->createOTP($user);
        return response()->json([
            'OTP is sent'
        ]);
    }

    /**
     * Generate Referral Code
     *
     * @param  [string] name
     */
    public function generateReferralCode()
    {
        do {
            $code = strtoupper(Str::random(6));
            $codeExists = User::where('referral_code', $code)->exists();
        } while ($codeExists);
        return $code;
    }
}
