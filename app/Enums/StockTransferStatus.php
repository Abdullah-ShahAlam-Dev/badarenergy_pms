<?php

namespace App\Enums;

enum StockTransferStatus: string
{
    case DRAFT = 'draft';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case DISPATCHED = 'dispatched';
    case IN_TRANSIT = 'in_transit';
    case PARTIALLY_RECEIVED = 'partially_received';
    case RECEIVED = 'received';
    case CANCELLED = 'cancelled';
    case REJECTED = 'rejected';
}
