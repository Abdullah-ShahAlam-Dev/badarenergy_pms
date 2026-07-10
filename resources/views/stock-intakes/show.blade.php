@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- Action Toolbar -->
        <div class="d-flex my-3 justify-content-between">
            <div>
                <a href="{{ route('stock-intakes.index') }}" class="btn btn-secondary border-0 btn-sm mr-2">
                    <i class="fa fa-arrow-left"></i> Back to Listing
                </a>
            </div>
            
            <div class="d-flex">
                @if ($voucher->status === 'pending' && ($approvePermission === 'all' || in_array('admin', user_roles())))
                    <button type="button" class="btn btn-primary btn-sm mr-2" id="approve-btn">
                        <i class="fa fa-check mr-1"></i> Approve & Post Stock
                    </button>
                @endif

                @if ($voucher->serials->count() > 0)
                    <a href="{{ route('stock-intakes.print-barcodes', [$voucher->id]) }}" target="_blank" class="btn btn-info btn-sm">
                        <i class="fa fa-barcode mr-1"></i> Print All Barcodes
                    </a>
                @endif
            </div>
        </div>

        <div class="row">
            <!-- Left Info Panel -->
            <div class="col-md-4">
                <div class="card bg-white border-0 b-shadow-4 p-20 rounded">
                    <h5 class="f-18 font-weight-bold text-dark mb-4">Voucher Summary</h5>

                    <div class="mb-3">
                        <span class="text-muted f-12 d-block text-uppercase">Voucher Number</span>
                        <strong class="f-15 text-dark">{{ $voucher->voucher_number }}</strong>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted f-12 d-block text-uppercase">Destination Warehouse</span>
                        <strong class="f-15 text-dark">{{ $voucher->warehouse ? $voucher->warehouse->name : '-' }}</strong>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted f-12 d-block text-uppercase">Linked Shipment</span>
                        <strong class="f-15 text-dark">
                            @if ($voucher->shipment)
                                <a href="{{ route('shipments.show', $voucher->shipment->id) }}" class="text-primary font-weight-bold">
                                    {{ $voucher->shipment->shipment_number }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </strong>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted f-12 d-block text-uppercase">Intake Date</span>
                        <strong class="f-15 text-dark">{{ $voucher->intake_date ? $voucher->intake_date->format(company()->date_format) : '-' }}</strong>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted f-12 d-block text-uppercase">Status</span>
                        @php
                            $statusColors = [
                                'draft' => 'badge-secondary',
                                'pending' => 'badge-warning',
                                'approved' => 'badge-info',
                                'completed' => 'badge-success',
                                'cancelled' => 'badge-danger',
                            ];
                            $badgeClass = $statusColors[$voucher->status] ?? 'badge-light';
                        @endphp
                        <span class="badge {{ $badgeClass }} px-2 py-1">{{ ucwords($voucher->status) }}</span>
                    </div>

                    <div class="mb-3">
                        <span class="text-muted f-12 d-block text-uppercase">Created By</span>
                        <strong class="f-15 text-dark">{{ $voucher->creator ? $voucher->creator->name : '-' }}</strong>
                    </div>

                    @if ($voucher->remarks)
                        <div class="mb-3">
                            <span class="text-muted f-12 d-block text-uppercase">Remarks</span>
                            <p class="f-14 text-dark mb-0">{{ $voucher->remarks }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Line Items Panel -->
            <div class="col-md-8">
                <div class="card bg-white border-0 b-shadow-4 p-20 rounded mb-4">
                    <h5 class="f-18 font-weight-bold text-dark mb-4">Line Items Received</h5>

                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-right">Qty Declared</th>
                                <th class="text-right">Qty Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($voucher->items as $item)
                                <tr>
                                    <td>
                                        {{ $item->product ? $item->product->name : '-' }}
                                    </td>
                                    <td class="text-right">{{ number_format($item->quantity_declared, 2) }}</td>
                                    <td class="text-right font-weight-bold">{{ number_format($item->quantity_received, 2) }}</td>


                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Generated Serials Panel -->
                @if ($voucher->serials->count() > 0)
                    <div class="card bg-white border-0 b-shadow-4 p-20 rounded">
                        <h5 class="f-18 font-weight-bold text-dark mb-4">Generated Serials & Barcodes ({{ $voucher->serials->count() }})</h5>

                        <div class="row">
                            @foreach ($voucher->serials as $serial)
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-2 text-center bg-light">
                                        <span class="f-14 font-weight-bold text-dark d-block mb-1">{{ $serial->serial_number }}</span>
                                        <small class="text-muted d-block mb-2">Product ID: {{ $serial->product_id }}</small>
                                        <a href="{{ route('stock-intakes.print-single-barcode', [$voucher->id, $serial->id]) }}" target="_blank" class="btn btn-outline-info btn-xs">
                                            <i class="fa fa-print"></i> Print Label
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Approve action handler
            $('#approve-btn').click(function() {
                Swal.fire({
                    title: "Approve and Post Stock?",
                    text: "This will commit stock quantities to warehouse inventory, calculate WAC, and generate serials.",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "Approve and Post",
                    cancelButtonText: "@lang('app.cancel')",
                    customClass: {
                        confirmButton: 'btn btn-primary mr-3',
                        cancelButton: 'btn btn-secondary'
                    },
                    showClass: {
                        popup: 'swal2-noanimation',
                        backdrop: 'swal2-noanimation'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        var url = "{{ route('stock-intakes.approve', [$voucher->id]) }}";
                        $.easyAjax({
                            type: 'POST',
                            url: url,
                            data: {
                                '_token': "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    window.location.reload();
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
