<?php

namespace App\Enums;

enum DeliveryOrderStatus: string
{
    case DRAFT = 'draft';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case PICKING = 'picking';
    case DISPATCHED = 'dispatched';
    case PARTIALLY_DELIVERED = 'partially_delivered';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
}
