<?php

namespace App\Enums;

enum SerialStatus: string
{
    case AVAILABLE = 'available';
    case SOLD = 'sold';
    case FAULTY = 'faulty';
    case IN_TRANSIT = 'in_transit';
    case RECEIVING = 'receiving';
    case QC_PENDING = 'qc_pending';
    case RESERVED = 'reserved';
    case DISPATCHED = 'dispatched';
    case DELIVERED = 'delivered';
    case WARRANTY_CLAIM = 'warranty_claim';
    case REPLACEMENT_ISSUED = 'replacement_issued';
    case RETURNED = 'returned';
    case REFURBISHED = 'refurbished';
    case SCRAPPED = 'scrapped';
    case INTERNAL_USE = 'internal_use';
}
