<?php

namespace App\Http\Controllers;

use App\DataTables\DealerAgingDataTable;
use App\DataTables\SalespersonAgingDataTable;
use App\Helper\Reply;
use App\Models\User;
use App\Models\ClientDetails;
use App\Services\DealerAgingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgingReportController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Aging & Outstanding Analysis';
    }

    /**
     * Display Dealer-wise Aging report.
     */
    public function index(DealerAgingDataTable $dataTable)
    {
        abort_403(user()->permission('view_aging_report') == 'none');

        $this->dealers = User::allClients();
        $this->salespersons = User::allEmployees();
        $this->cities = ClientDetails::whereNotNull('city')->groupBy('city')->pluck('city');
        $this->tiers = ClientDetails::whereNotNull('dealer_tier')->groupBy('dealer_tier')->pluck('dealer_tier');
        $this->activeTab = 'dealer';

        return $dataTable->render('reports.aging.index', $this->data);
    }

    /**
     * Display Salesperson-wise Aging report.
     */
    public function salespersonReport(SalespersonAgingDataTable $dataTable)
    {
        abort_403(user()->permission('view_salesperson_aging') == 'none');

        $this->salespersons = User::allEmployees();
        $this->cities = ClientDetails::whereNotNull('city')->groupBy('city')->pluck('city');
        $this->activeTab = 'salesperson';

        return $dataTable->render('reports.aging.salesperson', $this->data);
    }

    /**
     * Force rebuild snapshot cache.
     */
    public function syncSnapshots()
    {
        abort_403(!in_array('admin', user_roles()));

        $service = new DealerAgingService();
        $service->regenerateAllSnapshots();

        // Audit Log
        try {
            \App\Models\DealerLedgerAuditLog::create([
                'company_id' => company() ? company()->id : null,
                'user_id' => user()->id,
                'ip_address' => request()->ip(),
                'action' => 'sync_snapshots',
                'reason' => 'Manual refresh of aging snapshot cache',
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // ignore logging error
        }

        return Reply::success("Snapshot Cache rebuilt successfully.");
    }
}
