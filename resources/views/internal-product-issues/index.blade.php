@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Internal Product Issues</h4>
        <div>
            <a href="{{ route('care-of-ledgers.index') }}" class="btn btn-outline-info btn-sm rounded mr-2">
                <i class="fa fa-book mr-1"></i> Care Of Ledgers
            </a>
            <a href="{{ route('internal-product-issues.create') }}" class="btn btn-primary btn-sm rounded">
                <i class="fa fa-plus mr-1"></i> Issue Product Internally
            </a>
        </div>
    </div>

    <div class="card bg-white border-0 shadow-sm rounded">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Ref #</th>
                            <th>Issued To (Care Of)</th>
                            <th>Issued Items Summary</th>
                            <th>Debit Amount</th>
                            <th>Linked DO</th>
                            <th>Linked Gate Pass</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($issues as $issue)
                            <tr>
                                <td>
                                    <span class="font-weight-bold text-dark">{{ $issue->reference_number }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('care-of-ledgers.show', $issue->care_of_id) }}" class="font-weight-bold">
                                        {{ $issue->careOfUser->name ?? 'User #'.$issue->care_of_id }}
                                    </a>
                                </td>
                                <td>{{ $issue->description }}</td>
                                <td><span class="badge badge-light border text-dark">{{ currency_format($issue->debit) }}</span></td>
                                <td>
                                    @if($issue->deliveryOrder)
                                        <a href="{{ route('delivery-orders.show', $issue->deliveryOrder->id) }}" target="_blank" class="badge badge-info">
                                            {{ $issue->deliveryOrder->delivery_order_number }}
                                        </a>
                                    @else
                                        --
                                    @endif
                                </td>
                                <td>
                                    @if($issue->gatePassRequest)
                                        <span class="badge badge-success">{{ $issue->gatePassRequest->request_number }}</span>
                                    @else
                                        --
                                    @endif
                                </td>
                                <td>{{ $issue->date ? $issue->date->format('Y-m-d H:i') : '--' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No internal product issues logged yet. Click "Issue Product Internally" to create one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
