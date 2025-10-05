<?php

namespace App\Enums;

enum ApiErrorCode: int
{
    case SOMETHING_WENT_WRONG = 250;
    case PAYMENT_ALREADY_MADE = 251;
}
