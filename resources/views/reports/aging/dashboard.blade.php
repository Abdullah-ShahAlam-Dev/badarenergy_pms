@extends('layouts.app')

@push('styles')
    <style>
        .kpi-card {
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .kpi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.06);
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }
        .kpi-red::before { background-color: #d9534f; }
        .kpi-green::before { background-color: #5cb85c; }
        .kpi-blue::before { background-color: #0275d8; }
        .kpi-yellow::before { background-color: #f0ad4e; }
        .kpi-purple::before { background-color: #6f42c1; }
    </style>
@endpush

@section('content')
    <div class="content-wrapper">
        <!-- Submenu Tab bar -->
        <div class="d-flex flex-column flex-md-row justify-content-between pb-3 border-bottom-grey">
            <div class="d-flex align-items-center">
                <a href="{{ route('aging.index') }}" class="btn btn-outline-secondary mr-2">Dealer Aging Report</a>
                <a href="{{ route('aging.salesperson') }}" class="btn btn-outline-secondary mr-2">Salesperson Report</a>
                <a href="{{ route('aging.dashboard') }}" class="btn btn-secondary">Aging Dashboard</a>
            </div>
        </div>

        <!-- KPI widgets -->
        <div class="row mt-4">
            <!-- Total Outstanding -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow-sm kpi-card kpi-red bg-white p-3 border-0">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Outstanding</div>
                    <div class="h4 mb-0 font-weight-bold text-dark-grey">
                        {{ currency_format($totalOutstanding, company()->currency_id) }}
                    </div>
                    <div class="text-lightest f-12 mt-1">Net receivable pool</div>
                </div>
            </div>

            <!-- Current Receivables -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow-sm kpi-card kpi-green bg-white p-3 border-0">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Current Receivables</div>
                    <div class="h4 mb-0 font-weight-bold text-dark-grey">
                        {{ currency_format($currentReceivables, company()->currency_id) }}
                    </div>
                    <div class="text-lightest f-12 mt-1">Not yet overdue</div>
                </div>
            </div>

            <!-- Overdue Receivables -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow-sm kpi-card kpi-yellow bg-white p-3 border-0">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Overdue Receivables</div>
                    <div class="h4 mb-0 font-weight-bold text-dark-grey">
                        {{ currency_format($overdueReceivables, company()->currency_id) }}
                    </div>
                    <div class="text-lightest f-12 mt-1">Awaiting recovery</div>
                </div>
            </div>

            <!-- Days Sales Outstanding (DSO) -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow-sm kpi-card kpi-blue bg-white p-3 border-0">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Average Collection Days (DSO)</div>
                    <div class="h4 mb-0 font-weight-bold text-dark-grey">
                        {{ $averageCollectionDays }} Days
                    </div>
                    <div class="text-lightest f-12 mt-1">Average days to recover credit</div>
                </div>
            </div>

            <!-- Today's Collection -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow-sm kpi-card kpi-green bg-white p-3 border-0">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Today's Recovery</div>
                    <div class="h4 mb-0 font-weight-bold text-dark-grey">
                        {{ currency_format($todayCollection, company()->currency_id) }}
                    </div>
                    <div class="text-lightest f-12 mt-1">Collected today</div>
                </div>
            </div>

            <!-- Monthly Collection -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow-sm kpi-card kpi-blue bg-white p-3 border-0">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Month-To-Date Recovery</div>
                    <div class="h4 mb-0 font-weight-bold text-dark-grey">
                        {{ currency_format($monthCollection, company()->currency_id) }}
                    </div>
                    <div class="text-lightest f-12 mt-1">Collected this month</div>
                </div>
            </div>

            <!-- Credit Exceeded Count -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow-sm kpi-card kpi-red bg-white p-3 border-0">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Credit Exceeded Accounts</div>
                    <div class="h4 mb-0 font-weight-bold text-dark-grey">
                        {{ $creditExceededCount }} Dealers
                    </div>
                    <div class="text-lightest f-12 mt-1">Exceeded credit limit</div>
                </div>
            </div>

            <!-- Average Outstanding -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow-sm kpi-card kpi-purple bg-white p-3 border-0">
                    <div class="text-xs font-weight-bold text-purple text-uppercase mb-1">Average Outstanding / Dealer</div>
                    <div class="h4 mb-0 font-weight-bold text-dark-grey">
                        {{ currency_format($averageOutstanding, company()->currency_id) }}
                    </div>
                    <div class="text-lightest f-12 mt-1">Per active debtor</div>
                </div>
            </div>
        </div>

        <!-- Defaulter Highlights & Trends Section -->
        <div class="row mt-2">
            <!-- Trend Chart -->
            <div class="col-lg-8 mb-4">
                <div class="card border-0 bg-white p-4 shadow-sm h-100 rounded-lg">
                    <h4 class="mb-4 f-18 font-weight-bold text-dark-grey">Outstanding Balance Trend</h4>
                    <div class="chart-container" style="position: relative; height:280px;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Highlight widgets -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 bg-white p-4 shadow-sm h-100 rounded-lg">
                    <h4 class="mb-4 f-18 font-weight-bold text-dark-grey">Extreme Exposure Areas</h4>
                    
                    <!-- Oldest Invoice -->
                    <div class="mb-4 pb-3 border-bottom-grey">
                        <div class="text-xs text-danger font-weight-bold text-uppercase mb-1">Oldest Outstanding Invoice</div>
                        @if($oldestInvoice)
                            <div class="f-15 font-weight-bold text-dark-grey">
                                <a href="{{ route('invoices.show', $oldestInvoice->id) }}" class="text-primary">{{ $oldestInvoice->invoice_number }}</a>
                            </div>
                            <div class="f-13 text-dark-grey mt-1">{{ currency_format($oldestInvoice->due_amount, company()->currency_id) }}</div>
                            <div class="f-12 text-lightest mt-1">Issued on: {{ $oldestInvoice->issue_date->format(company()->date_format) }} ({{ $oldestInvoice->issue_date->diffInDays(now()) }} days ago)</div>
                        @else
                            <div class="f-14 text-lightest">None</div>
                        @endif
                    </div>

                    <!-- Largest Invoice -->
                    <div>
                        <div class="text-xs text-danger font-weight-bold text-uppercase mb-1">Largest Outstanding Invoice</div>
                        @if($largestInvoice)
                            <div class="f-15 font-weight-bold text-dark-grey">
                                <a href="{{ route('invoices.show', $largestInvoice->id) }}" class="text-primary">{{ $largestInvoice->invoice_number }}</a>
                            </div>
                            <div class="f-13 text-dark-grey mt-1">{{ currency_format($largestInvoice->due_amount, company()->currency_id) }}</div>
                            <div class="f-12 text-lightest mt-1">Issued to: {{ $largestInvoice->client->name ?? '--' }}</div>
                        @else
                            <div class="f-14 text-lightest">None</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 10 Debtor Accounts -->
        <div class="card border-0 mt-2 bg-white p-4 shadow-sm rounded-lg">
            <h4 class="mb-4 f-18 font-weight-bold text-dark-grey">Top 10 Exposure Accounts</h4>
            <div class="table-responsive">
                <table class="table table-hover border-0 w-100">
                    <thead>
                        <tr class="text-dark-grey font-weight-bold f-14 bg-light">
                            <th>#</th>
                            <th>Dealer Name</th>
                            <th>Credit Limit</th>
                            <th class="text-right">Current Balance</th>
                            <th class="text-right">Overdue Balance</th>
                            <th class="text-right">Net Outstanding</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topDefaulters as $def)
                            <tr class="f-14">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('ledgers.show', $def->dealer_id) }}" class="text-primary font-weight-bold">
                                        {{ $def->dealer->name ?? '--' }}
                                    </a>
                                </td>
                                <td>{{ currency_format($def->dealer->clientDetails->credit_limit ?? 0.00, company()->currency_id) }}</td>
                                <td class="text-right text-success">{{ currency_format($def->current, company()->currency_id) }}</td>
                                <td class="text-right text-warning">
                                    {{ currency_format($def->outstanding - $def->current, company()->currency_id) }}
                                </td>
                                <td class="text-right text-danger font-weight-bold">
                                    {{ currency_format($def->outstanding, company()->currency_id) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-lightest f-14">No Exposure accounts.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/chart.js/chart.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            const ctx = document.getElementById('trendChart').getContext('2d');
            const labels = {!! json_encode(array_keys($trendData)) !!};
            const values = {!! json_encode(array_values($trendData)) !!};

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Outstanding Balance',
                        data: values,
                        borderColor: '#d9534f',
                        backgroundColor: 'rgba(217, 83, 79, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#d9534f',
                        pointRadius: 4,
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f5f5f5'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
@endpush
