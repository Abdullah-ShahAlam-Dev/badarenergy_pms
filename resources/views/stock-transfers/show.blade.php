@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <!-- Stock Transfer Details Section -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-lg p-4 bg-white mb-4">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-4">
                    <h4 class="text-dark-grey"><i class="fa fa-info-circle text-primary"></i> Stock Transfer #{{ $transfer->transfer_number }}</h4>
                    
                    @php
                        $statusColors = [
                            'draft' => 'secondary',
                            'pending_approval' => 'warning',
                            'approved' => 'primary',
                            'dispatched' => 'info',
                            'in_transit' => 'info',
                            'partially_received' => 'warning',
                            'received' => 'success',
                            'cancelled' => 'dark',
                            'rejected' => 'danger'
                        ];
                        $color = $statusColors[$transfer->status] ?? 'secondary';
                    @endphp
                    <span class="badge badge-{{ $color }} p-2" style="font-size: 14px;">{{ ucfirst(str_replace('_', ' ', $transfer->status)) }}</span>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-lightest f-12 text-uppercase d-block mb-1">Source Warehouse</label>
                        <span class="f-15 text-dark font-weight-bold">{{ $transfer->sourceWarehouse->name }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-lightest f-12 text-uppercase d-block mb-1">Destination Warehouse/Outlet</label>
                        <span class="f-15 text-dark font-weight-bold">{{ $transfer->destinationWarehouse->name }}</span>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-6 mb-3">
                        <label class="text-lightest f-12 text-uppercase d-block mb-1">Vehicle Number</label>
                        <span class="f-14 text-dark font-weight-bold">{{ $transfer->vehicle_number ?: '--' }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-lightest f-12 text-uppercase d-block mb-1">Driver Name</label>
                        <span class="f-14 text-dark font-weight-bold">{{ $transfer->driver_name ?: '--' }}</span>
                    </div>
                </div>

                @if($transfer->remarks)
                    <div class="mt-2 p-3 bg-light rounded mb-3">
                        <label class="text-lightest f-11 text-uppercase d-block mb-1">Remarks</label>
                        <span class="f-13 text-dark-grey">{{ $transfer->remarks }}</span>
                    </div>
                @endif

                <!-- Items list -->
                <h5 class="text-dark-grey mt-4 mb-3 border-bottom pb-2">Line Items & Serial Inventory</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Product Model</th>
                                <th>Serials Locked</th>
                                <th style="text-align: right;">Qty Requested</th>
                                <th style="text-align: right;">Qty Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transfer->items as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($item->serials as $ts)
                                                @php
                                                    $tsColor = $ts->status === 'received' ? 'success' : ($ts->status === 'dispatched' ? 'info' : 'secondary');
                                                @endphp
                                                <span class="badge badge-{{ $tsColor }} mr-1">{{ $ts->serial->serial_number }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td style="text-align: right;">{{ number_format($item->quantity, 2) }}</td>
                                    <td style="text-align: right; font-weight: bold;" class="{{ $item->quantity_received >= $item->quantity ? 'text-success' : 'text-warning' }}">
                                        {{ number_format($item->quantity_received, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Sticky Actions Bar -->
                <div class="mt-4 border-top pt-3 d-flex flex-wrap gap-2 justify-content-start">
                    <!-- Approval Triggers -->
                    @if(in_array($transfer->status, ['draft', 'pending_approval']) && user()->permission('approve_stock_transfer') != 'none')
                        <button class="btn btn-success mr-2" onclick="processApproval('approve')"><i class="fa fa-check"></i> Approve</button>
                        <button class="btn btn-danger mr-2" onclick="promptRejection()"><i class="fa fa-times"></i> Reject</button>
                    @endif

                    <!-- Dispatch Trigger -->
                    @if(in_array($transfer->status, ['draft', 'approved']) && user()->permission('dispatch_stock_transfer') != 'none')
                        <button class="btn btn-primary mr-2" onclick="showDispatchModal()"><i class="fa fa-truck"></i> Dispatch Shipment</button>
                    @endif

                    <!-- Receipt Trigger -->
                    @if(in_array($transfer->status, ['dispatched', 'in_transit', 'partially_received']) && user()->permission('receive_stock_transfer') != 'none')
                        <button class="btn btn-info mr-2" onclick="showReceiptModal()"><i class="fa fa-box-open"></i> Process Receipt</button>
                    @endif

                    <!-- Cancel Trigger -->
                    @if(!in_array($transfer->status, ['received', 'completed', 'cancelled', 'rejected']) && user()->permission('cancel_stock_transfer') != 'none')
                        <button class="btn btn-dark mr-2" onclick="cancelTransfer()"><i class="fa fa-ban"></i> Cancel Transfer</button>
                    @endif

                    <!-- Print Triggers -->
                    @if(user()->permission('print_stock_transfer') != 'none')
                        <a href="{{ route('stock-transfers.print_challan', $transfer->id) }}" target="_blank" class="btn btn-outline-secondary mr-2"><i class="fa fa-print"></i> Print Gate Challan</a>
                        @if(in_array($transfer->status, ['partially_received', 'received']))
                            <a href="{{ route('stock-transfers.print_grn', $transfer->id) }}" target="_blank" class="btn btn-outline-success mr-2"><i class="fa fa-file-invoice"></i> Print GRN</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- History Audit Timeline Column -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-lg p-4 bg-white mb-4">
                <h4 class="text-dark-grey border-bottom pb-2 mb-3"><i class="fa fa-history text-secondary"></i> Action Timeline</h4>
                
                <div class="timelineTimeline">
                    <div class="timelineItem pb-3 border-left pl-3 position-relative">
                        <div class="timelineBadge bg-primary text-white p-1 rounded-circle d-inline-block text-center mr-2 position-absolute" style="left: -13px; top: 0; width: 26px; height: 26px;"><i class="fa fa-plus f-10"></i></div>
                        <span class="f-13 font-weight-bold text-dark">Draft Request Created</span>
                        <div class="text-lightest f-11">by {{ $transfer->creator->name }}</div>
                        <div class="text-lightest f-10">{{ $transfer->created_at->format('Y-m-d H:i') }} (IP: {{ $transfer->created_ip }})</div>
                    </div>

                    @if($transfer->approved_at)
                        <div class="timelineItem pb-3 border-left pl-3 position-relative">
                            <div class="timelineBadge bg-success text-white p-1 rounded-circle d-inline-block text-center mr-2 position-absolute" style="left: -13px; top: 0; width: 26px; height: 26px;"><i class="fa fa-check f-10"></i></div>
                            <span class="f-13 font-weight-bold text-dark">Transfer Approved / Processed</span>
                            <div class="text-lightest f-11">by {{ $transfer->approver->name ?? '--' }}</div>
                            <div class="text-lightest f-10">{{ $transfer->approved_at->format('Y-m-d H:i') }} (IP: {{ $transfer->approved_ip }})</div>
                        </div>
                    @endif

                    @if($transfer->dispatched_at)
                        <div class="timelineItem pb-3 border-left pl-3 position-relative">
                            <div class="timelineBadge bg-info text-white p-1 rounded-circle d-inline-block text-center mr-2 position-absolute" style="left: -13px; top: 0; width: 26px; height: 26px;"><i class="fa fa-truck f-10"></i></div>
                            <span class="f-13 font-weight-bold text-dark">Shipment Dispatched</span>
                            <div class="text-lightest f-11">by {{ $transfer->dispatcher->name ?? '--' }}</div>
                            <div class="text-lightest f-10">{{ $transfer->dispatched_at->format('Y-m-d H:i') }} (IP: {{ $transfer->dispatched_ip }})</div>
                        </div>
                    @endif

                    @if($transfer->received_at)
                        <div class="timelineItem pb-3 position-relative">
                            <div class="timelineBadge bg-success text-white p-1 rounded-circle d-inline-block text-center mr-2 position-absolute" style="left: -13px; top: 0; width: 26px; height: 26px;"><i class="fa fa-box-open f-10"></i></div>
                            <span class="f-13 font-weight-bold text-dark">Shipment Received (GRN)</span>
                            <div class="text-lightest f-11">by {{ $transfer->receiver->name ?? '--' }}</div>
                            <div class="text-lightest f-10">{{ $transfer->received_at->format('Y-m-d H:i') }} (IP: {{ $transfer->received_ip }})</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log rejection popup modal block -->
<script>
    function processApproval(action) {
        Swal.fire({
            title: 'Approve Transfer?',
            text: 'Are you sure you want to approve this stock transfer request?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve',
            cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'btn btn-success mr-3', cancelButton: 'btn btn-secondary' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.easyAjax({
                    url: "{{ route('stock-transfers.approve', $transfer->id) }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (res) { location.reload(); }
                });
            }
        });
    }

    function promptRejection() {
        Swal.fire({
            title: 'Reject Transfer',
            input: 'textarea',
            inputLabel: 'Please provide a reason for rejection:',
            inputPlaceholder: 'Enter rejection reason...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Reject',
            cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'btn btn-danger mr-3', cancelButton: 'btn btn-secondary' },
            buttonsStyling: false,
            inputValidator: (value) => { if (!value) return 'Rejection reason is required.'; }
        }).then((result) => {
            if (result.isConfirmed) {
                $.easyAjax({
                    url: "{{ route('stock-transfers.reject', $transfer->id) }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}", reason: result.value },
                    success: function (res) { location.reload(); }
                });
            }
        });
    }

    function showDispatchModal() {
        Swal.fire({
            title: 'Dispatch Shipment',
            html: `<div class="text-left">
                <label class="f-14 text-dark-grey">Vehicle Number</label>
                <input id="swal-vehicle" class="swal2-input" value="{{ $transfer->vehicle_number }}" placeholder="e.g. LHR-9988">
                <label class="f-14 text-dark-grey mt-2">Driver Name</label>
                <input id="swal-driver" class="swal2-input" value="{{ $transfer->driver_name }}" placeholder="e.g. Muhammad Ali">
            </div>`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-truck"></i> Dispatch Now',
            cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
            buttonsStyling: false,
            preConfirm: () => {
                return {
                    vehicle_number: document.getElementById('swal-vehicle').value,
                    driver_name: document.getElementById('swal-driver').value
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.easyAjax({
                    url: "{{ route('stock-transfers.dispatch', $transfer->id) }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        vehicle_number: result.value.vehicle_number,
                        driver_name: result.value.driver_name
                    },
                    success: function (res) { location.reload(); }
                });
            }
        });
    }

    function showReceiptModal() {
        Swal.fire({
            title: 'Confirm Receipt (GRN)',
            text: 'Process complete receipt of all dispatched serials at the destination warehouse?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-box-open"></i> Confirm Receipt',
            cancelButtonText: 'Cancel',
            customClass: { confirmButton: 'btn btn-success mr-3', cancelButton: 'btn btn-secondary' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                let items = {!! json_encode($transfer->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'serials' => $item->serials->map(fn($ts) => $ts->serial->serial_number)->toArray()
                    ];
                })) !!};

                $.easyAjax({
                    url: "{{ route('stock-transfers.receive', $transfer->id) }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}", items: items },
                    success: function (res) { location.reload(); }
                });
            }
        });
    }

    function cancelTransfer() {
        Swal.fire({
            title: 'Cancel Transfer?',
            text: 'Are you sure you want to cancel this active transfer request? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Cancel Transfer',
            cancelButtonText: 'Keep Transfer',
            customClass: { confirmButton: 'btn btn-danger mr-3', cancelButton: 'btn btn-secondary' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.easyAjax({
                    url: "{{ route('stock-transfers.cancel', $transfer->id) }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (res) { location.reload(); }
                });
            }
        });
    }
</script>
@endsection
