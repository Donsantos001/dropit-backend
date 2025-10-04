<?php

namespace App\Enums;

enum OrderStatus: string
{
    case CREATED = 'created';
    case OPEN = 'open';
    case PROGRESS = 'progress';
    case CANCELLED = 'cancelled';
}
