@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Care Of / Owner Ledgers</h4>
        <a href="{{ route('internal-product-issues.create') }}" class="btn btn-primary btn-sm rounded">
            <i class="fa fa-plus mr-1"></i> Issue Product
        </a>
    </div>

    <div class="card bg-white border-0 shadow-sm rounded">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Care Of Person / Employee</th>
                            <th>Total Debit (Issued Value)</th>
                            <th>Total Credit (Settlements)</th>
                            <th>Current Outstanding Balance</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($careOfUsers as $user)
                            <tr>
                                <td>
                                    <a href="{{ route('care-of-ledgers.show', $user->id) }}" class="font-weight-bold text-dark">
                                        {{ $user->name }}
                                    </a>
                                    <small class="text-muted d-block">{{ $user->email }}</small>
                                </td>
                                <td><span class="text-dark">{{ currency_format($user->total_debit) }}</span></td>
                                <td><span class="text-success">{{ currency_format($user->total_credit) }}</span></td>
                                <td>
                                    @if($user->current_balance > 0)
                                        <span class="badge badge-warning text-dark px-3 py-1 font-weight-bold">{{ currency_format($user->current_balance) }}</span>
                                    @else
                                        <span class="badge badge-success px-3 py-1">{{ currency_format($user->current_balance) }}</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('care-of-ledgers.show', $user->id) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fa fa-file-text-o mr-1"></i> Statement / Ledger
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No Care Of ledger activity recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
