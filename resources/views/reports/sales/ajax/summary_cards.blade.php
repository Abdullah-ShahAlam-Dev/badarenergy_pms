<div class="row mb-4">
    <!-- Total Sales -->
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
        <div class="card shadow-sm border-0 bg-white p-3 rounded-lg" style="border-left: 4px solid #d9534f;">
            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Sales</div>
            <div class="h5 mb-0 font-weight-bold text-dark-grey">{{ currency_format($stats['totalSales'] ?? 0.00) }}</div>
            <div class="text-lightest f-11 mt-1">Billed Invoices</div>
        </div>
    </div>

    <!-- Invoices Count -->
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
        <div class="card shadow-sm border-0 bg-white p-3 rounded-lg" style="border-left: 4px solid #0275d8;">
            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Invoices</div>
            <div class="h5 mb-0 font-weight-bold text-dark-grey">{{ number_format($stats['invoiceCount'] ?? 0) }}</div>
            <div class="text-lightest f-11 mt-1">Transaction Count</div>
        </div>
    </div>

    <!-- Collections -->
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
        <div class="card shadow-sm border-0 bg-white p-3 rounded-lg" style="border-left: 4px solid #5cb85c;">
            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Collections</div>
            <div class="h5 mb-0 font-weight-bold text-dark-grey">{{ currency_format($stats['collections'] ?? 0.00) }}</div>
            <div class="text-lightest f-11 mt-1">Recovered Cash</div>
        </div>
    </div>

    <!-- Outstanding -->
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
        <div class="card shadow-sm border-0 bg-white p-3 rounded-lg" style="border-left: 4px solid #f0ad4e;">
            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Outstanding</div>
            <div class="h5 mb-0 font-weight-bold text-dark-grey">{{ currency_format($stats['outstanding'] ?? 0.00) }}</div>
            <div class="text-lightest f-11 mt-1">Net Receivables</div>
        </div>
    </div>

    <!-- Quantity Sold -->
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
        <div class="card shadow-sm border-0 bg-white p-3 rounded-lg" style="border-left: 4px solid #5bc0de;">
            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Quantity Sold</div>
            <div class="h5 mb-0 font-weight-bold text-dark-grey">{{ number_format($stats['quantitySold'] ?? 0) }}</div>
            <div class="text-lightest f-11 mt-1">Units Sold</div>
        </div>
    </div>

    <!-- Average Sale -->
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
        <div class="card shadow-sm border-0 bg-white p-3 rounded-lg" style="border-left: 4px solid #6f42c1;">
            <div class="text-xs font-weight-bold text-purple text-uppercase mb-1">Average Sale</div>
            <div class="h5 mb-0 font-weight-bold text-dark-grey">{{ currency_format($stats['averageSale'] ?? 0.00) }}</div>
            <div class="text-lightest f-11 mt-1">Per Invoice Value</div>
        </div>
    </div>
</div>
