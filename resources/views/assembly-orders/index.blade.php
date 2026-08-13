@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0 font-weight-bold">Assembly Parts Stock Withdrawals</h4>
        <a href="{{ route('assembly-orders.create') }}" class="btn btn-primary btn-sm rounded font-weight-bold">
            <i class="fa fa-plus mr-1"></i> Withdraw Assembly Parts
        </a>
    </div>

    <div class="card bg-white border-0 shadow-sm rounded">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Ref #</th>
                            <th>Stock Withdrawn By</th>
                            <th>Warehouse</th>
                            <th>Assembly Parts Withdrawn</th>
                            <th>Date / Time</th>
                            <th>Inventory Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assemblyOrders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('assembly-orders.show', $order->id) }}" class="font-weight-bold text-dark">
                                        {{ $order->assembly_number }}
                                    </a>
                                </td>
                                <td>
                                    <span class="font-weight-bold text-primary">
                                        <i class="fa fa-user mr-1"></i>{{ $order->creator->name ?? 'User' }}
                                    </span>
                                </td>
                                <td>{{ $order->warehouse->name ?? '--' }}</td>
                                <td>
                                    @php
                                        $summary = $order->items->map(function($i) {
                                            return $i->quantity_used . 'x ' . ($i->rawProduct ? $i->rawProduct->name : 'Part');
                                        })->implode(', ');
                                    @endphp
                                    <span class="f-13 text-dark font-weight-bold">{{ \Illuminate\Support\Str::limit($summary ?: 'Parts', 60) }}</span>
                                    <span class="badge badge-light border text-muted ml-1">({{ $order->items->count() }} items)</span>
                                </td>
                                <td>{{ $order->created_at ? $order->created_at->format('Y-m-d H:i') : '--' }}</td>
                                <td>
                                    <span class="badge badge-success px-2 py-1"><i class="fa fa-check-circle mr-1"></i>Issued & Deducted</span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('assembly-orders.show', $order->id) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fa fa-eye mr-1"></i> View Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No Assembly Stock Withdrawals found. Click "Withdraw Assembly Parts" to issue stock.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
