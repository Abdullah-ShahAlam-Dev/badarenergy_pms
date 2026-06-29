<?php

namespace App\Http\Controllers;

use App\Services\TransferPrintService;

class TransferPrintController extends AccountBaseController
{
    protected $printService;

    public function __construct()
    {
        parent::__construct();
        $this->printService = new TransferPrintService();
    }

    public function printChallan($id)
    {
        abort_403(user()->permission('print_stock_transfer') == 'none');
        
        $data = $this->printService->getChallanData($id);
        $this->transfer = $data['transfer'];
        
        return view('stock-transfers.print.challan', $this->data);
    }

    public function printGRN($id)
    {
        abort_403(user()->permission('print_stock_transfer') == 'none');
        
        $data = $this->printService->getGRNData($id);
        $this->transfer = $data['transfer'];
        
        return view('stock-transfers.print.grn', $this->data);
    }
}
