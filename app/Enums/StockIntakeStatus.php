<?php

namespace App\Enums;

enum StockIntakeStatus: string
{
    case DRAFT = 'draft';
    case RECEIVING = 'receiving';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
