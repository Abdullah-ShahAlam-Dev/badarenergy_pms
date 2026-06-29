<?php

namespace App\Enums;

enum StockIssueStatus: string
{
    case DRAFT = 'draft';
    case APPROVED = 'approved';
    case DISPATCHED = 'dispatched';
    case CANCELLED = 'cancelled';
}
