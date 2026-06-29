<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItems;
use App\Models\Payment;
use App\Models\CreditNotes;
use App\Models\DealerAgingSnapshot;
use Illuminate\Support\Facades\DB;

class SalesReportsService
{
    protected function hasJoin($query, string $tableName)
    {
        $joins = $query->getQuery()->joins ?? [];
        foreach ($joins as $join) {
            if (is_string($join->table) && strpos($join->table, $tableName) !== false) {
                return true;
            }
        }
        return false;
    }
    /**
     * Apply default database query parameters to filter invoices.
     */
    public function applyInvoiceFilters($query, array $filters)
    {
        $companyId = company() ? company()->id : null;
        if ($companyId) {
            $query->where('invoices.company_id', $companyId);
        }

        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $query->whereBetween('invoices.issue_date', [$filters['startDate'], $filters['endDate']]);
        }

        if (!empty($filters['dealerId']) && $filters['dealerId'] !== 'all') {
            $query->where('invoices.client_id', $filters['dealerId']);
        }

        if (!empty($filters['salespersonId']) && $filters['salespersonId'] !== 'all') {
            $query->join('client_details as cd_inv', 'cd_inv.user_id', '=', 'invoices.client_id')
                ->where('cd_inv.salesperson_id', $filters['salespersonId']);
        }

        if (!empty($filters['warehouseId']) && $filters['warehouseId'] !== 'all') {
            $query->where('invoices.warehouse_id', $filters['warehouseId']);
        }

        if (!empty($filters['city']) && $filters['city'] !== 'all') {
            if (!$this->hasJoin($query, 'client_details')) {
                $query->join('client_details as cd_inv', 'cd_inv.user_id', '=', 'invoices.client_id');
            }
            $query->where('cd_inv.city', $filters['city']);
        }

        if (!empty($filters['productId']) && $filters['productId'] !== 'all') {
            $query->whereExists(function ($q) use ($filters) {
                $q->select(DB::raw(1))
                    ->from('invoice_items')
                    ->whereColumn('invoice_items.invoice_id', 'invoices.id')
                    ->where('invoice_items.product_id', $filters['productId']);
            });
        }

        if (!empty($filters['modelId']) && $filters['modelId'] !== 'all') {
            $query->whereExists(function ($q) use ($filters) {
                $q->select(DB::raw(1))
                    ->from('invoice_items')
                    ->join('products', 'products.id', '=', 'invoice_items.product_id')
                    ->whereColumn('invoice_items.invoice_id', 'invoices.id')
                    ->where('products.sub_category_id', $filters['modelId']);
            });
        }

        if (!empty($filters['invoiceStatus']) && $filters['invoiceStatus'] !== 'all') {
            $query->where('invoices.status', $filters['invoiceStatus']);
        } else {
            $query->whereIn('invoices.status', ['paid', 'unpaid', 'partial']);
        }

        // Salesperson user restriction constraint
        if (!in_array('admin', user_roles())) {
            if (!$this->hasJoin($query, 'client_details')) {
                $query->join('client_details as cd_inv_restrict', 'cd_inv_restrict.user_id', '=', 'invoices.client_id');
            }
            // Bind parameter dynamically
            $alias = $this->hasJoin($query, 'cd_inv_restrict') ? 'cd_inv_restrict' : 'cd_inv';
            $query->where($alias . '.salesperson_id', user()->id);
        }

        return $query;
    }

    /**
     * Apply default database query parameters to filter payments.
     */
    public function applyPaymentFilters($query, array $filters)
    {
        $companyId = company() ? company()->id : null;
        if ($companyId) {
            $query->where('payments.company_id', $companyId);
        }

        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $query->whereBetween('payments.paid_on', [$filters['startDate'], $filters['endDate']]);
        }

        if (!empty($filters['dealerId']) && $filters['dealerId'] !== 'all') {
            $query->where('payments.customer_id', $filters['dealerId']);
        }

        if (!empty($filters['salespersonId']) && $filters['salespersonId'] !== 'all') {
            $query->join('client_details as cd_pay', 'cd_pay.user_id', '=', 'payments.customer_id')
                ->where('cd_pay.salesperson_id', $filters['salespersonId']);
        }

        if (!empty($filters['city']) && $filters['city'] !== 'all') {
            if (!$this->hasJoin($query, 'client_details')) {
                $query->join('client_details as cd_pay', 'cd_pay.user_id', '=', 'payments.customer_id');
            }
            $query->where('cd_pay.city', $filters['city']);
        }

        if (!empty($filters['paymentStatus']) && $filters['paymentStatus'] !== 'all') {
            $query->where('payments.status', $filters['paymentStatus']);
        } else {
            $query->where('payments.status', 'complete');
        }

        // Salesperson user restriction constraint
        if (!in_array('admin', user_roles())) {
            if (!$this->hasJoin($query, 'client_details')) {
                $query->join('client_details as cd_pay_restrict', 'cd_pay_restrict.user_id', '=', 'payments.customer_id');
            }
            $alias = $this->hasJoin($query, 'cd_pay_restrict') ? 'cd_pay_restrict' : 'cd_pay';
            $query->where($alias . '.salesperson_id', user()->id);
        }

        return $query;
    }

    /**
     * Calculate summary metrics from filters for the header summary cards.
     */
    public function calculateSummaryStats(array $filters): array
    {
        $invoiceQuery = Invoice::query();
        $this->applyInvoiceFilters($invoiceQuery, $filters);
        
        $salesStats = (clone $invoiceQuery)
            ->selectRaw('SUM(invoices.total) as total_sales, SUM(invoices.sub_total) as gross_sales, COUNT(invoices.id) as invoice_count')
            ->first();

        $totalSales = (float)($salesStats->total_sales ?? 0.00);
        $grossSales = (float)($salesStats->gross_sales ?? 0.00);
        $invoiceCount = (int)($salesStats->invoice_count ?? 0);

        // Fetch Returns
        $returnsQuery = CreditNotes::query()
            ->join('invoices', 'invoices.id', '=', 'credit_notes.invoice_id');
        
        $companyId = company() ? company()->id : null;
        if ($companyId) {
            $returnsQuery->where('credit_notes.company_id', $companyId);
        }
        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $returnsQuery->whereBetween('invoices.issue_date', [$filters['startDate'], $filters['endDate']]);
        }
        if (!empty($filters['dealerId']) && $filters['dealerId'] !== 'all') {
            $returnsQuery->where('invoices.client_id', $filters['dealerId']);
        }
        if (!empty($filters['salespersonId']) && $filters['salespersonId'] !== 'all') {
            $returnsQuery->join('client_details as cd_cn', 'cd_cn.user_id', '=', 'invoices.client_id')
                ->where('cd_cn.salesperson_id', $filters['salespersonId']);
        }
        if (!empty($filters['warehouseId']) && $filters['warehouseId'] !== 'all') {
            $returnsQuery->where('invoices.warehouse_id', $filters['warehouseId']);
        }
        if (!empty($filters['city']) && $filters['city'] !== 'all') {
            if (!$this->hasJoin($returnsQuery, 'client_details')) {
                $returnsQuery->join('client_details as cd_cn', 'cd_cn.user_id', '=', 'invoices.client_id');
            }
            $returnsQuery->where('cd_cn.city', $filters['city']);
        }
        if (!in_array('admin', user_roles())) {
            if (!$this->hasJoin($returnsQuery, 'client_details')) {
                $returnsQuery->join('client_details as cd_cn_restrict', 'cd_cn_restrict.user_id', '=', 'invoices.client_id');
            }
            $alias = $this->hasJoin($returnsQuery, 'cd_cn_restrict') ? 'cd_cn_restrict' : 'cd_cn';
            $returnsQuery->where($alias . '.salesperson_id', user()->id);
        }
        $totalReturns = (float)$returnsQuery->sum('credit_notes.total');

        $netSales = $grossSales - $totalReturns;

        // Fetch Payments
        $paymentQuery = Payment::query();
        $this->applyPaymentFilters($paymentQuery, $filters);
        $totalCollections = (float)$paymentQuery->sum('payments.amount');

        // Outstanding Outstanding = Total Sales - Collections
        $outstanding = $totalSales - $totalCollections - $totalReturns;
        if ($outstanding < 0) {
            $outstanding = 0.00;
        }

        $averageSale = $invoiceCount > 0 ? ($totalSales / $invoiceCount) : 0.00;
        $recoveryRatio = $totalSales > 0 ? ($totalCollections / $totalSales) * 100 : 0.00;

        // Quantity Sold
        $qtyQuery = InvoiceItems::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id');
        
        if ($companyId) {
            $qtyQuery->where('invoices.company_id', $companyId);
        }
        if (!empty($filters['startDate']) && !empty($filters['endDate'])) {
            $qtyQuery->whereBetween('invoices.issue_date', [$filters['startDate'], $filters['endDate']]);
        }
        if (!empty($filters['dealerId']) && $filters['dealerId'] !== 'all') {
            $qtyQuery->where('invoices.client_id', $filters['dealerId']);
        }
        if (!empty($filters['salespersonId']) && $filters['salespersonId'] !== 'all') {
            $qtyQuery->join('client_details as cd_qty', 'cd_qty.user_id', '=', 'invoices.client_id')
                ->where('cd_qty.salesperson_id', $filters['salespersonId']);
        }
        if (!empty($filters['warehouseId']) && $filters['warehouseId'] !== 'all') {
            $qtyQuery->where('invoices.warehouse_id', $filters['warehouseId']);
        }
        if (!empty($filters['city']) && $filters['city'] !== 'all') {
            if (!$this->hasJoin($qtyQuery, 'client_details')) {
                $qtyQuery->join('client_details as cd_qty', 'cd_qty.user_id', '=', 'invoices.client_id');
            }
            $qtyQuery->where('cd_qty.city', $filters['city']);
        }
        if (!empty($filters['productId']) && $filters['productId'] !== 'all') {
            $qtyQuery->where('invoice_items.product_id', $filters['productId']);
        }
        if (!empty($filters['modelId']) && $filters['modelId'] !== 'all') {
            $qtyQuery->join('products as p_qty', 'p_qty.id', '=', 'invoice_items.product_id')
                ->where('p_qty.sub_category_id', $filters['modelId']);
        }
        if (!in_array('admin', user_roles())) {
            if (!$this->hasJoin($qtyQuery, 'client_details')) {
                $qtyQuery->join('client_details as cd_qty_restrict', 'cd_qty_restrict.user_id', '=', 'invoices.client_id');
            }
            $alias = $this->hasJoin($qtyQuery, 'cd_qty_restrict') ? 'cd_qty_restrict' : 'cd_qty';
            $qtyQuery->where($alias . '.salesperson_id', user()->id);
        }
        $quantitySold = (float)$qtyQuery->sum('invoice_items.quantity');

        return [
            'totalSales' => $totalSales,
            'grossSales' => $grossSales,
            'netSales' => $netSales,
            'invoiceCount' => $invoiceCount,
            'collections' => $totalCollections,
            'outstanding' => $outstanding,
            'returns' => $totalReturns,
            'averageSale' => $averageSale,
            'recoveryRatio' => $recoveryRatio,
            'quantitySold' => $quantitySold
        ];
    }
}
