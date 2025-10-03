<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Validator;


class LogoutController extends Controller
{
    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return ResponseBuilder::asSuccess()
            ->withMessage('Logged out successfully.')
            ->build();
    }
}
