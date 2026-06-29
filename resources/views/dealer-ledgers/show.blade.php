@extends('layouts.app')

@push('styles')
    @include('sections.daterange_css')
    <style>
        .summary-card {
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.05);
        }
    </style>
@endpush

@section('content')
    <div class="content-wrapper">
        <!-- Header Section -->
        <div class="d-flex flex-column flex-md-row justify-content-between pb-3 border-bottom-grey">
            <div>
                <h3 class="f-21 font-weight-bold text-dark-grey">{{ $dealer->name }}</h3>
                <p class="mb-0 text-lightest f-13">Dealer Detailed Financial Ledger</p>
            </div>
            <div class="d-flex align-items-center mt-3 mt-md-0">
                <a href="{{ route('ledgers.print', $dealer->id) }}?startDate={{ request('startDate') }}&endDate={{ request('endDate') }}" 
                   target="_blank" class="btn btn-secondary mr-2" icon="print">
                    <i class="fa fa-print"></i> Print Ledger
                </a>
                <a href="{{ route('ledgers.index') }}" class="btn btn-outline-secondary">
                    <i class="fa fa-arrow-left"></i> Receivables Summary
                </a>
            </div>
        </div>

        <!-- Filter panel -->
        <div class="card border-0 mt-4 bg-white p-3 rounded-lg shadow-sm">
            <form action="{{ route('ledgers.show', $dealer->id) }}" method="GET" id="filter-form">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label class="f-14 text-dark-grey font-weight-bold mb-2">Date Range Filter</label>
                        <div class="d-flex">
                            <input type="text" name="startDate" id="startDate" class="form-control height-35 f-14 mr-2" 
                                   value="{{ request('startDate') }}" placeholder="Start Date" autocomplete="off" />
                            <input type="text" name="endDate" id="endDate" class="form-control height-35 f-14" 
                                   value="{{ request('endDate') }}" placeholder="End Date" autocomplete="off" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary height-35 f-14 btn-block"><i class="fa fa-search"></i> Apply Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('ledgers.show', $dealer->id) }}" class="btn btn-light height-35 f-14 btn-block"><i class="fa fa-sync"></i> Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Summary KPI Cards -->
        <div class="row mt-4">
            <!-- Net Outstanding -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow-sm summary-card bg-white p-3 border-0">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Net Outstanding</div>
                    <div class="h5 mb-0 font-weight-bold text-dark-grey">
                        {{ currency_format($currentBalance, company()->currency_id) }}
                    </div>
                    <div class="text-lightest f-12 mt-1">Current running balance</div>
                </div>
            </div>

            <!-- Credit Limit -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow-sm summary-card bg-white p-3 border-0">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Credit Limit</div>
                    <div class="h5 mb-0 font-weight-bold text-dark-grey">
                        {{ currency_format($dealer->clientDetails->credit_limit ?? 0.00, company()->currency_id) }}
                    </div>
                    <div class="text-lightest f-12 mt-1">Authorized credit ceiling</div>
                </div>
            </div>

            <!-- Available Credit -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow-sm summary-card bg-white p-3 border-0">
                    @php
                        $limit = (float)($dealer->clientDetails->credit_limit ?? 0.00);
                        $avail = $limit - $currentBalance;
                    @endphp
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Available Credit</div>
                    <div class="h5 mb-0 font-weight-bold {{ $avail < 0 ? 'text-danger' : 'text-dark-grey' }}">
                        {{ currency_format($avail, company()->currency_id) }}
                    </div>
                    <div class="text-lightest f-12 mt-1">Balance credit headroom</div>
                </div>
            </div>

            <!-- Last Payment Date -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow-sm summary-card bg-white p-3 border-0">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Last Payment Date</div>
                    <div class="h5 mb-0 font-weight-bold text-dark-grey">
                        {{ $lastPaymentDate ? $lastPaymentDate->format(company()->date_format) : '--' }}
                    </div>
                    <div class="text-lightest f-12 mt-1">
                        @if ($lastPaymentDate)
                            {{ $lastPaymentDate->diffInDays(now()) }} days ago
                        @else
                            No recorded payments
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Meta Details Card -->
        <div class="card border-0 mt-2 bg-white p-3 rounded-lg shadow-sm">
            <div class="row f-14 text-dark-grey">
                <div class="col-md-3"><strong>Location:</strong> {{ $dealer->clientDetails->city ?? '--' }} / {{ $dealer->clientDetails->area ?? '--' }}</div>
                <div class="col-md-3"><strong>Salesperson:</strong> {{ $dealer->clientDetails->salesperson->name ?? '--' }}</div>
                <div class="col-md-3"><strong>NTN / STRN:</strong> {{ $dealer->clientDetails->ntn_number ?? '--' }} / {{ $dealer->clientDetails->strn_number ?? '--' }}</div>
                <div class="col-md-3"><strong>Category:</strong> {{ ucfirst($dealer->clientDetails->dealer_category ?? 'dealer') }}</div>
            </div>
        </div>

        <!-- Ledger Entries Table -->
        <div class="d-flex flex-column w-tables rounded bg-white mt-4 p-4 shadow-sm">
            <h4 class="mb-4 f-18 font-weight-bold border-bottom-grey pb-3">Transaction History</h4>

            <div class="table-responsive">
                <table class="table table-hover border-0 w-100" id="ledger-table">
                    <thead>
                        <tr class="text-dark-grey font-weight-bold f-14 bg-light">
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Type</th>
                            <th class="text-right">Debit (+)</th>
                            <th class="text-right">Credit (-)</th>
                            <th class="text-right">Running Balance</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Opening Balance Row if date filter applied -->
                        @if($startDate)
                            <tr class="f-14 bg-light font-weight-bold">
                                <td>{{ $startDate->format(company()->date_format) }}</td>
                                <td>--</td>
                                <td>Opening Balance</td>
                                <td class="text-right">--</td>
                                <td class="text-right">--</td>
                                <td class="text-right {{ $openingBalance > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ currency_format($openingBalance, company()->currency_id) }}
                                </td>
                                <td>Outstanding balance prior to date range</td>
                            </tr>
                        @endif

                        @forelse($ledgerEntries as $entry)
                            <tr class="f-14 {{ $entry->is_reversed ? 'text-muted text-decoration-line-through' : '' }}">
                                <td>{{ $entry->date->format(company()->date_format) }}</td>
                                <td>
                                    @if($entry->invoice_id)
                                        <a href="{{ route('invoices.show', $entry->invoice_id) }}" class="text-primary font-weight-bold">
                                            {{ $entry->reference_number }}
                                        </a>
                                    @elseif($entry->payment_id)
                                        <a href="{{ route('payments.show', $entry->payment_id) }}" class="text-primary font-weight-bold">
                                            {{ $entry->reference_number }}
                                        </a>
                                    @elseif($entry->credit_note_id)
                                        <a href="{{ route('creditnotes.show', $entry->credit_note_id) }}" class="text-primary font-weight-bold">
                                            {{ $entry->reference_number }}
                                        </a>
                                    @else
                                        <span class="font-weight-bold text-dark-grey">{{ $entry->reference_number }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-pill 
                                        @if($entry->transaction_type === 'invoice') badge-danger
                                        @elseif($entry->transaction_type === 'payment') badge-success
                                        @elseif($entry->transaction_type === 'credit_note') badge-info
                                        @elseif($entry->transaction_type === 'reversal') badge-warning
                                        @else badge-secondary
                                        @endif">
                                        {{ ucfirst($entry->transaction_type) }}
                                    </span>
                                </td>
                                <td class="text-right text-danger">
                                    {{ $entry->debit > 0 ? currency_format($entry->debit, company()->currency_id) : '--' }}
                                </td>
                                <td class="text-right text-success">
                                    {{ $entry->credit > 0 ? currency_format($entry->credit, company()->currency_id) : '--' }}
                                </td>
                                <td class="text-right font-weight-bold {{ $entry->balance > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ currency_format($entry->balance, company()->currency_id) }}
                                </td>
                                <td class="f-12 text-lightest">{{ $entry->remarks ?? '--' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-dark-grey f-14">
                                    No transaction entries found for the selected date range.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.daterange_js')
    <script>
        $(document).ready(function() {
            datepicker('#startDate', {
                position: 'bl',
                formatter: (input, date, instance) => {
                    input.value = moment(date).format('{{ company()->date_format_js }}');
                }
            });

            datepicker('#endDate', {
                position: 'bl',
                formatter: (input, date, instance) => {
                    input.value = moment(date).format('{{ company()->date_format_js }}');
                }
            });
        });
    </script>
@endpush
