<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case CANCELED = 'canceled';
    case PENDING_APPROVAL = 'pending_approval';
    case UNPAID = 'unpaid';
    case PAID = 'paid';
    case PARTIAL = 'partial';
}
