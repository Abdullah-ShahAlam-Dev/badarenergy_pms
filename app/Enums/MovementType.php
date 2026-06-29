<?php

namespace App\Enums;

enum MovementType: string
{
    case INTAKE = 'intake';
    case ISSUE = 'issue';
    case TRANSFER_OUT = 'transfer_out';
    case TRANSFER_IN = 'transfer_in';
    case ADJUSTMENT_UP = 'adjustment_up';
    case ADJUSTMENT_DOWN = 'adjustment_down';
    case RETURN = 'return';
    case SCRAP = 'scrap';
    case DAMAGE_IN_TRANSIT = 'damage_in_transit';
    case LEGACY_DEDUCTION = 'legacy_deduction';
}
