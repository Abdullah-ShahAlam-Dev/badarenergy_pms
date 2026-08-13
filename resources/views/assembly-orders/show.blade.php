@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0 font-weight-bold">Assembly Stock Withdrawal: {{ $assemblyOrder->assembly_number }}</h4>
        <a href="{{ route('assembly-orders.index') }}" class="btn btn-secondary btn-sm rounded">
            <i class="fa fa-arrow-left mr-1"></i> Back to Withdrawal Logs
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card bg-white border-0 shadow-sm rounded mb-4">
                <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
                    <span>Withdrawal Summary</span>
                    <span class="badge badge-success px-3 py-1"><i class="fa fa-check-circle mr-1"></i>Issued & Deducted</span>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <p class="mb-1 text-muted f-12 text-uppercase">Stock Withdrawn By:</p>
                            <h6 class="font-weight-bold text-primary f-16"><i class="fa fa-user mr-1"></i>{{ $assemblyOrder->creator ? $assemblyOrder->creator->name : 'User' }}</h6>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1 text-muted f-12 text-uppercase">Source Warehouse:</p>
                            <h6 class="font-weight-bold text-dark f-16"><i class="fa fa-warehouse mr-1"></i>{{ $assemblyOrder->warehouse->name ?? '--' }}</h6>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1 text-muted f-12 text-uppercase">Withdrawal Date & Time:</p>
                            <h6 class="font-weight-bold text-dark f-15">{{ $assemblyOrder->created_at ? $assemblyOrder->created_at->format('Y-m-d H:i') : '--' }}</h6>
                        </div>
                    </div>
                    @if($assemblyOrder->notes)
                        <div class="p-3 bg-light rounded text-dark">
                            <strong>Notes / Purpose:</strong> {{ $assemblyOrder->notes }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="card bg-white border-0 shadow-sm rounded mb-4">
                <div class="card-header bg-white font-weight-bold">
                    Assembly Parts Withdrawn & Deducted from Inventory
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Assembly Component / Part</th>
                                    <th class="text-right">Quantity Withdrawn & Deducted</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assemblyOrder->items as $item)
                                    <tr>
                                        <td class="font-weight-bold text-dark">
                                            {{ $item->rawProduct->name ?? 'Part #'.$item->raw_product_id }}
                                        </td>
                                        <td class="text-right font-weight-bold text-danger">
                                            -{{ $item->quantity_used ?: $item->quantity_required }} Pcs
                                        </td>
                                        <td>
                                            <span class="badge badge-success px-2 py-1"><i class="fa fa-check mr-1"></i>Deducted from {{ $assemblyOrder->warehouse->name ?? 'Warehouse' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-white border-0 shadow-sm rounded mb-4">
                <div class="card-header bg-white font-weight-bold">
                    User Audit Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="text-muted f-12 d-block text-uppercase">Technician / Authorized User</span>
                        <strong class="f-15 text-dark">{{ $assemblyOrder->creator ? $assemblyOrder->creator->name : 'User' }}</strong>
                        <small class="text-muted d-block">{{ $assemblyOrder->creator ? $assemblyOrder->creator->email : '' }}</small>
                    </div>
                    <div class="mb-3">
                        <span class="text-muted f-12 d-block text-uppercase">Reference Code</span>
                        <strong class="f-15 text-dark">{{ $assemblyOrder->assembly_number }}</strong>
                    </div>
                    <div>
                        <span class="text-muted f-12 d-block text-uppercase">Total Items Count</span>
                        <strong class="f-15 text-dark">{{ $assemblyOrder->items->count() }} Components</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
