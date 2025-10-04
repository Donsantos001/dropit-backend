<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case IN_TRANSIT = 'in_transit';
    case TRANSFERRED = 'transferred';
    case DELAYED = 'delayed';
    case DELIVERED = 'delivered';
}
