<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [
    //     'agent_id',
    //     'shipment_id',
    //     'active',
    //     'price',
    // ];


    /**
     * Get the user that owns this.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the shipment that owns this.
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class, 'shipment_id');
    }

    /**
     * Accept request
     */
    public function accept()
    {
        $shipment = $this->shipment;
        $shipment->update([
            'agent_id' => $this->agent_id,
            'price' => $this->price,
        ]);
        $shipment->order->update(['status' => 'progress']);
        $this->update(['active' => false]);
    }
}
