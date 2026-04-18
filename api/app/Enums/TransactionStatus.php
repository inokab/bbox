<?php

namespace App\Enums;
enum TransactionStatus: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
}
