<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductSubCategory;
use App\Models\ClientDetails;
use App\Services\SalesReportsService;
use App\DataTables\SalesReportDataTable;
use App\DataTables\DailySalesDataTable;
use App\DataTables\WeeklySalesDataTable;
use App\DataTables\MonthlySalesDataTable;
use App\DataTables\DealerSalesDataTable;
use App\DataTables\ProductSalesDataTable;
use App\DataTables\ModelSalesDataTable;
use App\DataTables\LocationSalesDataTable;
use App\DataTables\SalespersonSalesDataTable;
use App\DataTables\FinanceCollectionDataTable;
use App\DataTables\RecoveryReportDataTable;
use App\DataTables\OutstandingReportDataTable;
use App\DataTables\CashFlowReportDataTable;
use Illuminate\Http\Request;

class SalesReportController extends AccountBaseController
{
    protected $salesReportsService;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.salesReport';
        $this->salesReportsService = new SalesReportsService();
    }

    /**
     * Helper to load standard drop-down filter variables.
     */
    protected function loadFilterAndStatsData(): void
    {
        $this->dealers = User::allClients();
        $this->salespersons = User::allEmployees();
        $this->warehouses = Warehouse::all();
        $this->products = Product::all();
        $this->models = ProductSubCategory::all();
        $this->cities = ClientDetails::whereNotNull('city')->groupBy('city')->pluck('city');

        // Extract request filters
        $filters = request()->only([
            'startDate', 'endDate', 'dealerId', 'salespersonId', 'warehouseId', 'productId', 'modelId', 'city', 'invoiceStatus', 'paymentStatus'
        ]);

        if (empty($filters['startDate'])) {
            $filters['startDate'] = now($this->company->timezone)->startOfMonth()->toDateString();
            $filters['endDate'] = now($this->company->timezone)->toDateString();
        }

        $this->stats = $this->salesReportsService->calculateSummaryStats($filters);
    }

    public function index(SalesReportDataTable $dataTable)
    {
        if (!request()->ajax()) {
            $this->fromDate = now($this->company->timezone)->startOfMonth();
            $this->toDate = now($this->company->timezone);
        }

        $this->clients = User::allClients();

        return $dataTable->render('reports.sales.index', $this->data);
    }

    public function daily(DailySalesDataTable $dataTable)
    {
        abort_403(user()->permission('view_daily_sales') == 'none');
        $this->loadFilterAndStatsData();
        $this->activeTab = 'daily';
        return $dataTable->render('reports.sales.daily', $this->data);
    }

    public function weekly(WeeklySalesDataTable $dataTable)
    {
        abort_403(user()->permission('view_weekly_sales') == 'none');
        $this->loadFilterAndStatsData();
        $this->activeTab = 'weekly';
        return $dataTable->render('reports.sales.weekly', $this->data);
    }

    public function monthly(MonthlySalesDataTable $dataTable)
    {
        abort_403(user()->permission('view_monthly_sales') == 'none');
        $this->loadFilterAndStatsData();
        $this->activeTab = 'monthly';
        return $dataTable->render('reports.sales.monthly', $this->data);
    }

    public function dealer(DealerSalesDataTable $dataTable)
    {
        abort_403(user()->permission('view_dealer_sales') == 'none');
        $this->loadFilterAndStatsData();
        $this->activeTab = 'dealer';
        return $dataTable->render('reports.sales.dealer', $this->data);
    }

    public function product(ProductSalesDataTable $dataTable)
    {
        abort_403(user()->permission('view_product_sales') == 'none');
        $this->loadFilterAndStatsData();
        $this->activeTab = 'product';
        return $dataTable->render('reports.sales.product', $this->data);
    }

    public function model(ModelSalesDataTable $dataTable)
    {
        abort_403(user()->permission('view_model_sales') == 'none');
        $this->loadFilterAndStatsData();
        $this->activeTab = 'model';
        return $dataTable->render('reports.sales.model', $this->data);
    }

    public function location(LocationSalesDataTable $dataTable)
    {
        abort_403(user()->permission('view_location_sales') == 'none');
        $this->loadFilterAndStatsData();
        $this->activeTab = 'location';
        return $dataTable->render('reports.sales.location', $this->data);
    }

    public function salesperson(SalespersonSalesDataTable $dataTable)
    {
        abort_403(user()->permission('view_salesperson_sales') == 'none');
        $this->loadFilterAndStatsData();
        $this->activeTab = 'salesperson';
        return $dataTable->render('reports.sales.salesperson', $this->data);
    }

    public function finance(FinanceCollectionDataTable $dataTable)
    {
        abort_403(user()->permission('view_finance_collection') == 'none');
        $this->loadFilterAndStatsData();
        $this->activeTab = 'finance';
        return $dataTable->render('reports.sales.finance', $this->data);
    }

    public function recovery(RecoveryReportDataTable $dataTable)
    {
        abort_403(user()->permission('view_recovery_report') == 'none');
        $this->loadFilterAndStatsData();
        $this->activeTab = 'recovery';
        return $dataTable->render('reports.sales.recovery', $this->data);
    }

    public function outstanding(OutstandingReportDataTable $dataTable)
    {
        abort_403(user()->permission('view_outstanding_report') == 'none');
        $this->loadFilterAndStatsData();
        $this->activeTab = 'outstanding';
        return $dataTable->render('reports.sales.outstanding', $this->data);
    }

    public function cashflow(CashFlowReportDataTable $dataTable)
    {
        abort_403(user()->permission('view_cashflow_report') == 'none');
        $this->loadFilterAndStatsData();
        $this->activeTab = 'cashflow';
        return $dataTable->render('reports.sales.cashflow', $this->data);
    }
}
