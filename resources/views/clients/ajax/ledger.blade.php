<div class="row pb-5">
    <div class="col-lg-12 col-md-12">
        <div class="d-flex flex-column w-tables rounded bg-white p-20">
            <h4 class="mb-4 f-21 font-weight-normal text-capitalize border-bottom-grey pb-3">
                Dealer Ledger
            </h4>

            <div class="table-responsive">
                <table class="table table-hover border-0 w-100" id="ledger-table">
                    <thead>
                        <tr class="text-dark-grey font-weight-bold f-14">
                            <th>Date</th>
                            <th>Description</th>
                            <th>Reference</th>
                            <th class="text-right">Debit (+)</th>
                            <th class="text-right">Credit (-)</th>
                            <th class="text-right">Outstanding Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ledgerEntries as $entry)
                            <tr class="f-14">
                                <td>{{ \Carbon\Carbon::parse($entry->date)->format(company()->date_format) }}</td>
                                <td>{{ $entry->description }}</td>
                                <td>
                                    @if($entry->invoice_id)
                                        <a href="{{ route('invoices.show', $entry->invoice_id) }}" class="text-primary font-weight-bold">
                                            Invoice #{{ $entry->invoice_id }}
                                        </a>
                                    @elseif($entry->payment_id)
                                        <span class="text-dark-grey font-weight-bold">
                                            Payment #{{ $entry->payment_id }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-right text-danger font-weight-bold">
                                    {{ $entry->debit > 0 ? currency_format($entry->debit, company()->currency_id) : '-' }}
                                </td>
                                <td class="text-right text-success font-weight-bold">
                                    {{ $entry->credit > 0 ? currency_format($entry->credit, company()->currency_id) : '-' }}
                                </td>
                                <td class="text-right font-weight-bold {{ $entry->balance > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ currency_format($entry->balance, company()->currency_id) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-dark-grey f-14">
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
