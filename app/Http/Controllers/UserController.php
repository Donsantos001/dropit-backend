<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class UserController extends Controller
{
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return ResponseBuilder::asSuccess()
            ->withData(['user' => $request->user()])
            ->build();
    }

    /**
     * Get the referred users User
     *
     * @return [json] user object list
     */
    public function referred_users(Request $request)
    {
        $referrals = $request->user()->referrals;
        return ResponseBuilder::asSuccess()
            ->withData(['referrals' => $referrals])
            ->build();
    }
    /**
     * Get the user who referred
     *
     * @return [json] user object
     */
    public function referrer(Request $request)
    {
        return ResponseBuilder::asSuccess()
            ->withData(['referrer' => $request->user()->referrer])
            ->build();
    }
}
