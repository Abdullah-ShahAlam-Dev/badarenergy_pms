@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="card shadow-sm border-0 rounded-lg p-4 bg-white">
        <h4 class="text-dark-grey border-bottom pb-2 mb-4"><i class="fa fa-truck-loading text-primary"></i> WMS Stock Transfer Wizard</h4>

        <!-- Wizard Progress Bar -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between text-center font-weight-bold">
                    <span class="step-indicator text-primary" id="ind-1">1. Logistics</span>
                    <span class="step-indicator text-muted" id="ind-2">2. Warehouses</span>
                    <span class="step-indicator text-muted" id="ind-3">3. Products & Serials</span>
                    <span class="step-indicator text-muted" id="ind-4">4. Review & Submit</span>
                </div>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" id="wizard-progress" style="width: 25%;"></div>
                </div>
            </div>
        </div>

        <form id="create-transfer-form">
            @csrf

            <!-- Step 1: Logistics -->
            <div class="wizard-step" id="step-1">
                <h5 class="text-dark-grey mb-3">Logistic Details</h5>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="f-14 text-dark-grey">Vehicle Number</label>
                        <input type="text" class="form-control height-35 f-14" name="vehicle_number" id="vehicle_number" placeholder="e.g. LHR-9988">
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="f-14 text-dark-grey">Driver Name</label>
                        <input type="text" class="form-control height-35 f-14" name="driver_name" id="driver_name" placeholder="e.g. Muhammad Ali">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 form-group">
                        <label class="f-14 text-dark-grey">Remarks</label>
                        <textarea class="form-control f-14" name="remarks" id="remarks" rows="3" placeholder="Enter transit comments..."></textarea>
                    </div>
                </div>
                <div class="mt-4 border-top pt-3 d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next <i class="fa fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- Step 2: Warehouses -->
            <div class="wizard-step d-none" id="step-2">
                <h5 class="text-dark-grey mb-3">Select Warehouses</h5>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="f-14 text-dark-grey">Source Warehouse <span class="text-danger">*</span></label>
                        <select class="form-control height-35 f-14" name="source_warehouse_id" id="source_warehouse_id" required>
                            <option value="">Select Warehouse</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="f-14 text-dark-grey">Destination Warehouse/Outlet <span class="text-danger">*</span></label>
                        <select class="form-control height-35 f-14" name="destination_warehouse_id" id="destination_warehouse_id" required>
                            <option value="">Select Destination Outlet</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4 border-top pt-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" onclick="prevStep(1)"><i class="fa fa-arrow-left"></i> Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextStep(3)">Next <i class="fa fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- Step 3: Products & Serials -->
            <div class="wizard-step d-none" id="step-3">
                <h5 class="text-dark-grey mb-3">Add Products & Scan Serials</h5>
                
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="f-14 text-dark-grey">Select Product</label>
                        <select class="form-control height-35 f-14" id="temp_product_id">
                            <option value="">Select Product Model</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" data-serials="{{ json_encode($p->serials->pluck('serial_number')) }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="f-14 text-dark-grey">Quantity</label>
                        <input type="number" class="form-control height-35 f-14" id="temp_quantity" min="1" value="1">
                    </div>
                    <div class="col-md-2 form-group align-self-end">
                        <button type="button" class="btn btn-success btn-block" onclick="addProductItem()"><i class="fa fa-plus"></i> Add</button>
                    </div>
                </div>

                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-striped" id="selected-products-table">
                        <thead>
                            <tr>
                                <th>Product Model</th>
                                <th>Quantity</th>
                                <th>Serials Mapped</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="items-list">
                            <!-- Items added dynamically -->
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 border-top pt-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" onclick="prevStep(2)"><i class="fa fa-arrow-left"></i> Previous</button>
                    <button type="button" class="btn btn-primary" onclick="nextStep(4)">Next <i class="fa fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- Step 4: Review -->
            <div class="wizard-step d-none" id="step-4">
                <h5 class="text-dark-grey mb-3">Review & Submit Request</h5>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Source:</strong> <span id="rev-source">--</span><br>
                        <strong>Destination:</strong> <span id="rev-dest">--</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Vehicle No:</strong> <span id="rev-vehicle">--</span><br>
                        <strong>Driver Name:</strong> <span id="rev-driver">--</span>
                    </div>
                </div>

                <h6 class="text-dark-grey mt-4 border-bottom pb-2">Itemized Summary</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Product Model</th>
                                <th>Quantity</th>
                                <th>Serial Numbers</th>
                            </tr>
                        </thead>
                        <tbody id="rev-items">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 border-top pt-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" onclick="prevStep(3)"><i class="fa fa-arrow-left"></i> Previous</button>
                    <button type="submit" class="btn btn-success"><i class="fa fa-check-circle"></i> Submit Transfer</button>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
    let addedItems = [];

    function nextStep(step) {
        if (step === 2) {
            $("#ind-1").removeClass("text-primary").addClass("text-muted");
            $("#ind-2").removeClass("text-muted").addClass("text-primary");
            $("#step-1").addClass("d-none");
            $("#step-2").removeClass("d-none");
            $("#wizard-progress").css("width", "50%");
        } else if (step === 3) {
            let src = $("#source_warehouse_id").val();
            let dst = $("#destination_warehouse_id").val();
            if (!src || !dst) {
                Swal.fire({ icon: 'warning', title: 'Validation', text: 'Please select source and destination warehouses.', timer: 3000, showConfirmButton: false });
                return;
            }
            if (src === dst) {
                Swal.fire({ icon: 'error', title: 'Validation', text: 'Source and destination warehouses cannot be the same.', timer: 3000, showConfirmButton: false });
                return;
            }
            $("#ind-2").removeClass("text-primary").addClass("text-muted");
            $("#ind-3").removeClass("text-muted").addClass("text-primary");
            $("#step-2").addClass("d-none");
            $("#step-3").removeClass("d-none");
            $("#wizard-progress").css("width", "75%");
        } else if (step === 4) {
            if (addedItems.length === 0) {
                Swal.fire({ icon: 'warning', title: 'No Items', text: 'Please add at least one product line item.', timer: 3000, showConfirmButton: false });
                return;
            }
            // Populate Review Data
            $("#rev-source").text($("#source_warehouse_id option:selected").text());
            $("#rev-dest").text($("#destination_warehouse_id option:selected").text());
            $("#rev-vehicle").text($("#vehicle_number").val() || '--');
            $("#rev-driver").text($("#driver_name").val() || '--');

            let revItemsHtml = '';
            addedItems.forEach(item => {
                revItemsHtml += `<tr>
                    <td>${item.product_name}</td>
                    <td>${item.quantity}</td>
                    <td>${item.serials.join(', ') || '--'}</td>
                </tr>`;
            });
            $("#rev-items").html(revItemsHtml);

            $("#ind-3").removeClass("text-primary").addClass("text-muted");
            $("#ind-4").removeClass("text-muted").addClass("text-primary");
            $("#step-3").addClass("d-none");
            $("#step-4").removeClass("d-none");
            $("#wizard-progress").css("width", "100%");
        }
    }

    function prevStep(step) {
        if (step === 1) {
            $("#ind-2").removeClass("text-primary").addClass("text-muted");
            $("#ind-1").removeClass("text-muted").addClass("text-primary");
            $("#step-2").addClass("d-none");
            $("#step-1").removeClass("d-none");
            $("#wizard-progress").css("width", "25%");
        } else if (step === 2) {
            $("#ind-3").removeClass("text-primary").addClass("text-muted");
            $("#ind-2").removeClass("text-muted").addClass("text-primary");
            $("#step-3").addClass("d-none");
            $("#step-2").removeClass("d-none");
            $("#wizard-progress").css("width", "50%");
        } else if (step === 3) {
            $("#ind-4").removeClass("text-primary").addClass("text-muted");
            $("#ind-3").removeClass("text-muted").addClass("text-primary");
            $("#step-4").addClass("d-none");
            $("#step-3").removeClass("d-none");
            $("#wizard-progress").css("width", "75%");
        }
    }

    function addProductItem() {
        let pId = $("#temp_product_id").val();
        let pName = $("#temp_product_id option:selected").text();
        let qty = parseInt($("#temp_quantity").val());
        let allSerials = $("#temp_product_id option:selected").data("serials") || [];

        if (!pId || qty <= 0) {
            Swal.fire({ icon: 'warning', title: 'Validation', text: 'Please select a product and valid quantity.', timer: 3000, showConfirmButton: false });
            return;
        }

        // Auto-assign available serials (server validates the rest)
        let selectedSerials = allSerials.slice(0, qty);

        addedItems.push({
            product_id: pId,
            product_name: pName,
            quantity: qty,
            serials: selectedSerials
        });

        renderItemsTable();
    }

    function renderItemsTable() {
        let html = '';
        addedItems.forEach((item, index) => {
            html += `<tr>
                <td>${item.product_name}</td>
                <td>${item.quantity}</td>
                <td><span class="badge badge-light">${item.serials.join(', ')}</span></td>
                <td><button type="button" class="btn btn-xs btn-danger" onclick="removeItem(${index})"><i class="fa fa-trash"></i></button></td>
            </tr>`;
        });
        $("#items-list").html(html);
    }

    function removeItem(index) {
        addedItems.splice(index, 1);
        renderItemsTable();
    }

    // Submit request via AJAX
    $("#create-transfer-form").submit(function (e) {
        e.preventDefault();

        let postData = {
            _token: $("input[name=_token]").val(),
            source_warehouse_id: $("#source_warehouse_id").val(),
            destination_warehouse_id: $("#destination_warehouse_id").val(),
            vehicle_number: $("#vehicle_number").val(),
            driver_name: $("#driver_name").val(),
            remarks: $("#remarks").val(),
            items: addedItems
        };

        $.easyAjax({
            url: "{{ route('stock-transfers.store') }}",
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
