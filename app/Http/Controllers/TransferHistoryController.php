<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Services\TransferPrintService;

class TransferHistoryController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function timeline($id)
    {
        abort_403(user()->permission('view_transfer_history') == 'none');

        $this->transfer = StockTransfer::with([
            'sourceWarehouse',
            'destinationWarehouse',
            'creator',
            'approver',
            'dispatcher',
            'receiver',
            'canceller',
            'items.product',
            'items.serials.serial',
            'logs.user'
        ])->findOrFail($id);

        $this->pageTitle = 'Transfer Action History';
        $this->activeTab = 'timeline';

        return view('stock-transfers.show', $this->data);
    }
}
