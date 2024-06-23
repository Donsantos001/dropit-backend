<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Traits\Verification;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    use Verification;

    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     */

    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = $request->user();

        $request->user()->tokens()->where('name', 'Personal Access Token')->delete();
        $tokenResult = $user->createToken('Personal Access Token', ['*'], now()->addDays(2));
        $token = $tokenResult->plainTextToken;

        return response()->json([
            'message' => 'Log in successful',
            'accessToken' => $token,
            'token_type' => 'Bearer',
            'verified' => !!$user->email_verified_at
        ]);
    }
}
