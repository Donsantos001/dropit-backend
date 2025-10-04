<?php

namespace App\Enums;

enum RequestStatus: string
{
    case REQUESTED = 'requested';
    case CLOSED = 'closed';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
}
