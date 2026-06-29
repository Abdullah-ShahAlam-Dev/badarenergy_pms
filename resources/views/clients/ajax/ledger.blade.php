<div class="row pb-5">
    <div class="col-lg-12 col-md-12">
        <div class="d-flex flex-column w-tables rounded bg-white p-20 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom-grey pb-3">
                <h4 class="mb-0 f-21 font-weight-normal text-capitalize">
                    Dealer Financial Ledger
                </h4>
                <div>
                    <a href="{{ route('ledgers.print', $client->id) }}?startDate={{ request('startDate') }}&endDate={{ request('endDate') }}" 
                       target="_blank" class="btn btn-secondary mr-2" icon="print">
                        <i class="fa fa-print"></i> Print Ledger
                    </a>
                </div>
            </div>

            <!-- Date Range Filters -->
            <div class="card border-0 mb-4 bg-light p-3 rounded-lg">
                <div class="row align-items-end">
                    <div class="col-md-5">
                        <label class="f-14 text-dark-grey font-weight-bold mb-2">Filter Date Range</label>
                        <div class="d-flex">
                            <input type="text" id="tab_startDate" class="form-control height-35 f-14 mr-2" 
                                   value="{{ request('startDate') }}" placeholder="Start Date" autocomplete="off" />
                            <input type="text" id="tab_endDate" class="form-control height-35 f-14" 
                                   value="{{ request('endDate') }}" placeholder="End Date" autocomplete="off" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button id="btn-apply-tab-filter" class="btn btn-primary height-35 f-14 btn-block"><i class="fa fa-search"></i> Apply Filter</button>
                    </div>
                    <div class="col-md-2">
                        <button id="btn-reset-tab-filter" class="btn btn-light height-35 f-14 btn-block"><i class="fa fa-sync"></i> Reset</button>
                    </div>
                </div>
            </div>

            <!-- Metrics Cards -->
            <div class="row mb-4">
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="card border-0 bg-light p-3 rounded-lg shadow-sm">
                        <div class="text-xs text-danger font-weight-bold text-uppercase mb-1">Outstanding Balance</div>
                        <div class="h5 font-weight-bold text-dark-grey">
                            {{ currency_format($currentBalance, company()->currency_id) }}
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="card border-0 bg-light p-3 rounded-lg shadow-sm">
                        <div class="text-xs text-info font-weight-bold text-uppercase mb-1">Credit Limit</div>
                        <div class="h5 font-weight-bold text-dark-grey">
                            {{ currency_format($client->clientDetails->credit_limit ?? 0.00, company()->currency_id) }}
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3">
                    <div class="card border-0 bg-light p-3 rounded-lg shadow-sm">
                        @php
                            $limit = (float)($client->clientDetails->credit_limit ?? 0.00);
                            $avail = $limit - $currentBalance;
                        @endphp
                        <div class="text-xs text-success font-weight-bold text-uppercase mb-1">Available Credit</div>
                        <div class="h5 font-weight-bold {{ $avail < 0 ? 'text-danger' : 'text-dark-grey' }}">
                            {{ currency_format($avail, company()->currency_id) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction Table -->
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
                                <td>{{ \Carbon\Carbon::parse($entry->date)->format(company()->date_format) }}</td>
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
                                <td class="text-right text-danger font-weight-bold">
                                    {{ $entry->debit > 0 ? currency_format($entry->debit, company()->currency_id) : '--' }}
                                </td>
                                <td class="text-right text-success font-weight-bold">
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
                                    No ledger entries found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        datepicker('#tab_startDate', {
            position: 'bl',
            formatter: (input, date, instance) => {
                input.value = moment(date).format('{{ company()->date_format_js }}');
            }
        });

        datepicker('#tab_endDate', {
            position: 'bl',
            formatter: (input, date, instance) => {
                input.value = moment(date).format('{{ company()->date_format_js }}');
            }
        });

        // Trigger filter reload
        $('#btn-apply-tab-filter').click(function() {
            const start = $('#tab_startDate').val();
            const end = $('#tab_endDate').val();
            
            const url = "{{ route('clients.show', $client->id) }}?tab=ledger&startDate=" + encodeURIComponent(start) + "&endDate=" + encodeURIComponent(end);
            
            $('#active-tab-content').html(loaderHtml);
            $.easyAjax({
                url: url,
                type: "GET",
                success: function(response) {
                    $('#active-tab-content').html(response.html);
                }
            });
        });

        // Reset filter
        $('#btn-reset-tab-filter').click(function() {
            const url = "{{ route('clients.show', $client->id) }}?tab=ledger";
            $('#active-tab-content').html(loaderHtml);
            $.easyAjax({
                url: url,
                type: "GET",
                success: function(response) {
                    $('#active-tab-content').html(response.html);
                }
            });
        });
    });
</script>
