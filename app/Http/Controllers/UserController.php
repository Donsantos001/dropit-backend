<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Get the referred users User
     *
     * @return [json] user object
     */
    public function referred_users(Request $request)
    {
        return response()->json($request->user()->referrals);
    }
    /**
     * Get the user who referred
     *
     * @return [json] user object
     */
    public function referrer(Request $request)
    {
        return response()->json($request->user()->referrer);
    }
}
