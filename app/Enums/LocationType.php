<?php

namespace App\Enums;

enum LocationType: string
{
    case PICKUP = 'pickup';
    case DELIVERY = 'delivery';
    case CURRENT = 'current';
    case DESTINATION = 'destination';
}
