<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Traits\Verification;
use Exception;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Hashing\HashManager;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class RegisterController extends Controller
{

    use Verification;

    private ConnectionInterface $db;

    private User $user;

    private HashManager $hash;

    /**
     * Inject models into the constructor.
     */
    public function __construct(ConnectionInterface $db, User $user, HashManager $hash)
    {
        $this->db = $db;
        $this->user = $user;
        $this->hash = $hash;
    }

    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @return [string] message
     */
    public function register(RegisterRequest $request)
    {
        $this->db->beginTransaction();

        $referral_code = $this->generateReferralCode();
        $referred_by = null;
        if ($request->referred_by) {
            $referred_by = User::where('referral_code', $request->referred_by)->first()->id;
        }


        $user = new $this->user();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;
        $user->referred_by = $referred_by->id ?? null;
        $user->referral_code = $referral_code;
        $user->password = $this->hash->make($request->password);
        $user->save();

        // $tokenResult = $user->createToken('Personal Access Token', ['*'], now()->addDays(2));
        // $token = $tokenResult->plainTextToken;

        $this->db->commit();
        $this->createOTP($request);

        return ResponseBuilder::asSuccess()
            ->withData(['user' => $user, 'verified' => false,])
            ->withMessage('Registered successfully.')
            ->build();
    }

    /**
     * Resend OTP
     *
     * @param  [string] email
     * @param  [string] token
     */
    public function sendOTP(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return ResponseBuilder::asError(422)
                ->withMessage('User not found')
                ->build();
        }
        if ($user->email_verified_at) {
            return ResponseBuilder::asError(422)
                ->withMessage('Email already verified')
                ->build();
        }

        try {
            $this->createOTP($request);
        } catch (Exception $e) {
            return ResponseBuilder::asError(422)
                ->withMessage('Unable to send otp, try again')
                ->build();
        }
        return ResponseBuilder::asSuccess()
            ->withMessage('OTP sent successfully')
            ->build();
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
