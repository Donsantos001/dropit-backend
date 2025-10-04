<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [
    //     'item_name',
    //     'receiver_firstname',
    //     'receiver_lastname',
    //     'receiver_phone_no',
    //     'receiver_email',
    //     'delivery_address',
    //     'pickup_address',
    //     'status',
    //     'payment_method',
    //     'preferred_vehicle',
    //     'schedule_type',
    //     'schedule_time'
    // ];


    /**
     * Get the user that placed this.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the shipment attached this.
     */
    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class, 'order_id');
    }

    /**
     * Get the agent requested on this.
     */
    public function agent_requests(): HasMany
    {
        return $this->hasMany(AgentRequest::class, 'order_id');
    }
}
