<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
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
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|string|unique:users',
            'phone_no' => 'required|string|unique:users',
            'password' => 'required|string',
            'referred_by' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $referral_code = $this->generateReferralCode();
        $referredby = null;
        if ($request->filled('referral_by')) {
            $referredby = User::where('referral_by', $request->referral_code)->first()->id;
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
                'message' => 'Successfully created user!',
                'accessToken' => $token,
            ], 201);
        } else {
            return response()->json(['error' => 'Provide proper details']);
        }
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
