<?php

namespace App\Enums;

enum ScheduleType: string
{
    case NOW = 'now';
    case LATER = 'later';
}