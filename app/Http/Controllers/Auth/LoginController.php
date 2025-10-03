<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Traits\Verification;
use Illuminate\Support\Facades\Validator;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class LoginController extends Controller
{
    use Verification;

    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     */

    public function login(LoginRequest $request)
    {
        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return ResponseBuilder::asError(401)
                ->withMessage('Unable to login with provided credentials.')
                ->build();
        }

        $user = $request->user();

        if (!$user->email_verified_at) {
            $this->createOTP($request);

            return ResponseBuilder::asSuccess()
                ->withData([
                    'info' => 'OTP is sent and will expire in 5 minutes',
                    'verified' => false,
                ])
                ->withMessage('Logged in successfully.')
                ->build();
        }

        // $request->user()->tokens()->where('name', $request->get('device_name', 'accessToken'))->delete();
        $tokenResult = $user->createToken($request->get('device_name', 'accessToken'), ['*'], now()->addDays(2));
        $token = $tokenResult->plainTextToken;

        return ResponseBuilder::asSuccess()
            ->withData([
                'user' => $user,
                'accessToken' => $token,
                'token_type' => 'Bearer',
                'verified' => true
            ])
            ->withMessage('Logged in successfully.')
            ->build();
    }
}
