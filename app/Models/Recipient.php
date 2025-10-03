<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [
    //     'firstname',
    //     'lastname',
    //     'phone_no',
    //     'email',
    //     'address',
    // ];

    /**
     * Get the user that referred this.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the shipment that uses this.
     */
    // public function agent(): HasMany
    // {
    //     return $this->hasMany(Shipment::class, 'agent_id');
    // }
}
