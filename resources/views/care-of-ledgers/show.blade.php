@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Care Of Ledger: {{ $careOfUser->name }}</h4>
            <small class="text-muted">{{ $careOfUser->email }}</small>
        </div>
        <div>
            <button type="button" class="btn btn-success btn-sm rounded mr-2" data-toggle="modal" data-target="#settlementModal">
                <i class="fa fa-money mr-1"></i> Post Settlement / Payment
            </button>
            <a href="{{ route('care-of-ledgers.index') }}" class="btn btn-secondary btn-sm rounded">
                <i class="fa fa-arrow-left mr-1"></i> Back to Ledgers
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-white border-0 shadow-sm rounded p-3">
                <small class="text-muted d-block text-uppercase font-weight-bold">Total Issued Value (Debit)</small>
                <h3 class="text-dark font-weight-bold mb-0">{{ currency_format($ledgers->sum('debit')) }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-white border-0 shadow-sm rounded p-3">
                <small class="text-muted d-block text-uppercase font-weight-bold">Total Settlement (Credit)</small>
                <h3 class="text-success font-weight-bold mb-0">{{ currency_format($ledgers->sum('credit')) }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-white border-0 shadow-sm rounded p-3">
                <small class="text-muted d-block text-uppercase font-weight-bold">Current Outstanding Balance</small>
                <h3 class="{{ $currentBalance > 0 ? 'text-danger' : 'text-success' }} font-weight-bold mb-0">
                    {{ currency_format($currentBalance) }}
                </h3>
            </div>
        </div>
    </div>

    <div class="card bg-white border-0 shadow-sm rounded">
        <div class="card-header bg-white font-weight-bold">
            Statement of Account
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Reference #</th>
                            <th>Description</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Running Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ledgers as $entry)
                            <tr>
                                <td>{{ $entry->date ? $entry->date->format('Y-m-d H:i') : '--' }}</td>
                                <td>
                                    @if($entry->transaction_type === 'product_issue')
                                        <span class="badge badge-info">Product Issue</span>
                                    @elseif($entry->transaction_type === 'settlement')
                                        <span class="badge badge-success">Settlement</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($entry->transaction_type) }}</span>
                                    @endif
                                </td>
                                <td class="font-weight-bold text-dark">{{ $entry->reference_number ?: '--' }}</td>
                                <td>{{ $entry->description }}</td>
                                <td><span class="text-dark">{{ $entry->debit > 0 ? currency_format($entry->debit) : '-' }}</span></td>
                                <td><span class="text-success">{{ $entry->credit > 0 ? currency_format($entry->credit) : '-' }}</span></td>
                                <td>
                                    <span class="font-weight-bold {{ $entry->balance > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ currency_format($entry->balance) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No transactions found for this account.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Post Settlement -->
<div class="modal fade" id="settlementModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('care-of-ledgers.post-settlement', $careOfUser->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold text-success"><i class="fa fa-money mr-1"></i> Post Settlement / Payment Adjustment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Settlement Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ $currentBalance > 0 ? $currentBalance : '0.00' }}" required>
                    </div>
                    <div class="form-group">
                        <label>Reference # / Receipt No</label>
                        <input type="text" name="reference_number" class="form-control" placeholder="e.g. REC-2026-001">
                    </div>
                    <div class="form-group">
                        <label>Remarks / Notes</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Salary deduction adjustment, Cash return..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check mr-1"></i> Post Settlement</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
