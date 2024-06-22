<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'phone_no',
        'password',
        'referred_by',
        'referral_code'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user that referred this.
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /**
     * Get the users referred by this.
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    /**
     * Get the users orders.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    /**
     * Get the users orders.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'agent_id');
    }

    /**
     * Get the users referred by this.
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(Recipient::class, 'user_id');
    }

    /**
     * Get the users orders.
     */
    public function shipment_requests(): HasMany
    {
        return $this->hasMany(ShipmentRequest::class, 'agent_id');
    }
}
