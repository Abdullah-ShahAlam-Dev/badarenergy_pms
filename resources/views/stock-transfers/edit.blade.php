@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="card shadow-sm border-0 rounded-lg p-4 bg-white">
        <h4 class="text-dark-grey border-bottom pb-2 mb-4"><i class="fa fa-edit text-primary"></i> Edit Stock Transfer Request #{{ $transfer->transfer_number }}</h4>

        <form id="edit-transfer-form">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 form-group">
                    <label class="f-14 text-dark-grey">Source Warehouse <span class="text-danger">*</span></label>
                    <select class="form-control height-35 f-14" name="source_warehouse_id" id="source_warehouse_id" required>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ $transfer->source_warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label class="f-14 text-dark-grey">Destination Warehouse/Outlet <span class="text-danger">*</span></label>
                    <select class="form-control height-35 f-14" name="destination_warehouse_id" id="destination_warehouse_id" required>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ $transfer->destination_warehouse_id == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6 form-group">
                    <label class="f-14 text-dark-grey">Vehicle Number</label>
                    <input type="text" class="form-control height-35 f-14" name="vehicle_number" id="vehicle_number" value="{{ $transfer->vehicle_number }}">
                </div>

                <div class="col-md-6 form-group">
                    <label class="f-14 text-dark-grey">Driver Name</label>
                    <input type="text" class="form-control height-35 f-14" name="driver_name" id="driver_name" value="{{ $transfer->driver_name }}">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12 form-group">
                    <label class="f-14 text-dark-grey">Remarks</label>
                    <textarea class="form-control f-14" name="remarks" id="remarks" rows="3">{{ $transfer->remarks }}</textarea>
                </div>
            </div>

            <!-- Prepopulated items list section -->
            <div class="table-responsive mt-4">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Product Model</th>
                            <th>Quantity</th>
                            <th>Serials Mapped</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transfer->items as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td><span class="badge badge-light">{{ $item->serials->pluck('serial.serial_number')->implode(', ') }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 border-top pt-3 d-flex justify-content-end">
                <a href="{{ route('stock-transfers.show', $transfer->id) }}" class="btn btn-secondary mr-2">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    $("#edit-transfer-form").submit(function (e) {
        e.preventDefault();

        // Simplify data serial mapping for update submittals
        let postData = {
            _token: $("input[name=_token]").val(),
            source_warehouse_id: $("#source_warehouse_id").val(),
            destination_warehouse_id: $("#destination_warehouse_id").val(),
            vehicle_number: $("#vehicle_number").val(),
            driver_name: $("#driver_name").val(),
            remarks: $("#remarks").val(),
            items: {!! json_encode($transfer->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'serials' => $item->serials->map(fn($ts) => $ts->serial->serial_number)->toArray()
                ];
            })) !!}
        };

        $.easyAjax({
            url: "{{ route('stock-transfers.update', $transfer->id) }}",
            type: "POST",
            data: postData,
            success: function (res) {
                if (res.status === 'success' && res.redirectUrl) {
                    window.location.href = res.redirectUrl;
                }
            }
        });
    });
</script>
@endsection
