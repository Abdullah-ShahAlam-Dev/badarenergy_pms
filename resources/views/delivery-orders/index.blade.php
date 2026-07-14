@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="d-block d-lg-flex d-md-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                <h4 class="mb-0 pr-3 f-18 font-weight-bold text-dark-grey float-left">Delivery Orders</h4>
            </div>
            
            <div class="d-flex align-items-center">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                        placeholder="@lang('app.startTyping')">
                </div>
            </div>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive p-4">
            <table id="delivery-orders-table" class="table table-hover border-0 w-100">
                <thead>
                    <tr>
                        <th>DO ID</th>
                        <th>Invoice Ref</th>
                        <th>Customer</th>
                        <th>Salesperson</th>
                        <th>Issue Date</th>
                        <th>Dispatcher</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Scanning Modal -->
    <div class="modal fade" id="scanModal" tabindex="-1" role="dialog" aria-labelledby="scanModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-0 shadow-lg rounded">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="scanModalLabel">
                        <i class="fa fa-barcode mr-2"></i>Barcode Scanning Workflow - <span id="modal-do-number"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" id="btn-close-scan-modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light p-4">
                    <div id="scan-alert-area"></div>

                    <!-- Connection indicator -->
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-white border rounded">
                        <div>
                            <span class="font-weight-bold text-dark"><i class="fa fa-plug mr-1 text-success"></i> Scanner Connection Mode:</span>
                            <span class="badge badge-success px-2 py-1">Ready (Virtual Keyboard Input)</span>
                        </div>
                        <div class="text-right">
                            <small class="text-muted">Input will autofocus automatically. Keep cursor focused here.</small>
                        </div>
                    </div>

                    <!-- Input Box -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-3">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-dark">Scan Barcode / Serial Number</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-keyboard"></i></span>
                                    </div>
                                    <input type="text" class="form-control form-control-lg border-primary" id="barcode-input" placeholder="Scan or type barcode/serial here..." autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Checklist -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-3">
                            <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-list mr-1"></i> Delivery Items Checklist</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0 bg-white" id="modal-products-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Product / Model</th>
                                            <th class="text-right" style="width: 100px;">Ordered Qty</th>
                                            <th class="text-right" style="width: 100px;">Scanned Qty</th>
                                            <th class="text-right" style="width: 100px;">Remaining</th>
                                            <th class="text-center" style="width: 120px;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Rendered dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Live Scanned list -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-history mr-1"></i> Scanned Serials List</h6>
                            <ul class="list-group" id="scanned-serials-list" style="max-height: 180px; overflow-y: auto;">
                                <!-- Rendered dynamically -->
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btn-submit-scans" disabled>
                        <i class="fa fa-check mr-1"></i>Submit Scans & Dispatch
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')
    <script>
        $(function() {
            var table = $('#delivery-orders-table').DataTable({
                responsive: true,
                serverSide: true,
                processing: true,
                ajax: {
                    url: "{{ route('delivery-orders.index') }}",
                    data: function(d) {
                        d.searchText = $('#search-text-field').val();
                    }
                },
                language: {
                    "url": "{{ __($darkPlaystoreUrl ?? 'assets/plugins/datatables/language/' . ($settings->locale ?? 'en') . '.json') }}"
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'invoice_number', name: 'invoice_number' },
                    { data: 'client_name', name: 'client_name', orderable: false, searchable: false },
                    { data: 'salesperson', name: 'salesperson', orderable: false, searchable: false },
                    { data: 'issue_date', name: 'issue_date' },
                    { data: 'dispatcher', name: 'dispatcher' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-right' }
                ]
            });

            $('#search-text-field').on('keyup', function() {
                table.draw();
            });

            // State variables for scan session
            var currentDoId = null;
            var doLines = [];
            var scannedSerials = [];

            // Web Audio API Sound synthesizers
            function playSuccessSound() {
                try {
                    var ctx = new (window.AudioContext || window.webkitAudioContext)();
                    var osc = ctx.createOscillator();
                    var gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(800, ctx.currentTime);
                    gain.gain.setValueAtTime(0.05, ctx.currentTime);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.12);
                } catch (e) { console.error(e); }
            }

            function playErrorSound() {
                try {
                    var ctx = new (window.AudioContext || window.webkitAudioContext)();
                    var osc = ctx.createOscillator();
                    var gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(150, ctx.currentTime);
                    gain.gain.setValueAtTime(0.08, ctx.currentTime);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.3);
                } catch (e) { console.error(e); }
            }

            // Click scan trigger
            $('body').on('click', '.open-scan-modal', function() {
                var doId = $(this).data('do-id');
                currentDoId = doId;
                scannedSerials = [];
                $('#scan-alert-area').html('');
                $('#barcode-input').val('');

                // Load DO Details
                var url = "{{ route('delivery-orders.details', ':id') }}".replace(':id', doId);
                $.easyAjax({
                    url: url,
                    type: "GET",
                    success: function(response) {
                        $('#modal-do-number').text(response.delivery_order_number);
                        
                        // Map lines
                        doLines = response.lines.map(function(line) {
                            return {
                                id: line.id,
                                product_id: line.product_id,
                                product_name: line.product_name,
                                is_serialized: line.is_serialized,
                                quantity_requested: parseInt(line.quantity_requested),
                                scanned_qty: line.is_serialized ? 0 : parseInt(line.quantity_requested) // Non-serialized pre-filled
                            };
                        });

                        renderProductsTable();
                        renderScannedList();
                        $('#scanModal').modal('show');
                    }
                });
            });

            // Focus Lock Inside Modal
            $('#scanModal').on('shown.bs.modal', function () {
                $('#barcode-input').focus();
            });

            $('body').on('click', '#scanModal', function (e) {
                if (e.target.id !== 'barcode-input' && e.target.id !== 'btn-submit-scans' && !$(e.target).closest('.remove-serial').length && !$(e.target).closest('[data-dismiss="modal"]').length) {
                    $('#barcode-input').focus();
                }
            });

            var scanTimeout = null;

            function processScan(val) {
                if (scanTimeout) {
                    clearTimeout(scanTimeout);
                    scanTimeout = null;
                }

                if (val === '') {
                    return;
                }

                // Check duplicate scan locally
                var alreadyScanned = scannedSerials.some(function(item) {
                    return item.serial_number.toLowerCase() === val.toLowerCase();
                });

                if (alreadyScanned) {
                    playErrorSound();
                    showAlert('danger', 'Serial number <strong>' + val + '</strong> is already scanned in this session.');
                    return;
                }

                // Validate on server
                $('#barcode-input').prop('disabled', true);
                var url = "{{ route('delivery-orders.validate-serial', ':id') }}".replace(':id', currentDoId);
                
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        serial_number: val
                    },
                    success: function(response) {
                        $('#barcode-input').prop('disabled', false).focus();
                        
                        if (response.status === 'success') {
                            // Match line product
                            var matchedLine = doLines.find(function(line) {
                                return line.product_id == response.product_id;
                            });

                            if (!matchedLine) {
                                playErrorSound();
                                showAlert('danger', 'Validated serial matches product ' + response.product_name + ' but it is not ordered in this DO.');
                                return;
                            }

                            if (matchedLine.scanned_qty >= matchedLine.quantity_requested) {
                                playErrorSound();
                                showAlert('danger', 'Quantity limit exceeded for ' + response.product_name + '. Ordered: ' + matchedLine.quantity_requested);
                                return;
                            }

                            // Add to list
                            playSuccessSound();
                            scannedSerials.push({
                                serial_id: response.serial_id,
                                product_id: response.product_id,
                                product_name: response.product_name,
                                serial_number: response.serial_number
                            });

                            matchedLine.scanned_qty += 1;
                            showAlert('success', 'Scanned: <strong>' + response.serial_number + '</strong> (' + response.product_name + ') successfully.');
                            
                            renderProductsTable();
                            renderScannedList();
                        } else {
                            playErrorSound();
                            showAlert('danger', response.message || 'Invalid serial number scan.');
                        }
                    },
                    error: function(xhr) {
                        $('#barcode-input').prop('disabled', false).focus();
                        playErrorSound();
                        var errorMsg = 'Server validation failed.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        showAlert('danger', errorMsg);
                    }
                });
            }

            // Handle scan input key listeners
            $('#barcode-input').on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (scanTimeout) {
                        clearTimeout(scanTimeout);
                        scanTimeout = null;
                    }
                    var val = $(this).val().trim();
                    $(this).val('');
                    processScan(val);
                }
            });

            $('#barcode-input').on('input', function() {
                if (scanTimeout) {
                    clearTimeout(scanTimeout);
                }
                
                var val = $(this).val().trim();
                if (val !== '') {
                    scanTimeout = setTimeout(function() {
                        $('#barcode-input').val('');
                        processScan(val);
                    }, 400); // 400ms delay to detect complete barcode scanning sequences
                }
            });

            // Remove scanned serial
            $('body').on('click', '.remove-serial', function() {
                var sIndex = $(this).data('index');
                var removedItem = scannedSerials[sIndex];
                
                if (removedItem) {
                    var matchedLine = doLines.find(function(line) {
                        return line.product_id == removedItem.product_id;
                    });
                    if (matchedLine) {
                        matchedLine.scanned_qty -= 1;
                    }
                    scannedSerials.splice(sIndex, 1);
                    playSuccessSound();
                    
                    renderProductsTable();
                    renderScannedList();
                    showAlert('warning', 'Removed scanned serial.');
                }
            });

            // Submit scans action
            $('#btn-submit-scans').on('click', function() {
                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i>Submitting...');
                
                var serialList = scannedSerials.map(function(item) {
                    return item.serial_number;
                });

                var url = "{{ route('delivery-orders.submit-scanned-serials', ':id') }}".replace(':id', currentDoId);
                
                $.easyAjax({
                    url: url,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        serials: serialList
                    },
                    success: function(response) {
                        btn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i>Submit Scans & Dispatch');
                        if (response.status === 'success') {
                            $('#scanModal').modal('hide');
                            table.draw();
                        }
                    },
                    error: function() {
                        btn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i>Submit Scans & Dispatch');
                    }
                });
            });

            // UI Render helpers
            function renderProductsTable() {
                var html = '';
                var allComplete = true;

                doLines.forEach(function(line) {
                    var remaining = line.quantity_requested - line.scanned_qty;
                    var pct = (line.quantity_requested > 0) ? (line.scanned_qty / line.quantity_requested) * 100 : 100;
                    var badgeClass = 'badge-secondary';
                    var badgeText = 'Pending';

                    if (line.scanned_qty === line.quantity_requested) {
                        badgeClass = 'badge-success';
                        badgeText = 'Complete';
                    } else if (line.scanned_qty > 0) {
                        badgeClass = 'badge-info';
                        badgeText = 'Scanning';
                        allComplete = false;
                    } else {
                        allComplete = false;
                    }

                    html += '<tr>' +
                        '<td>' + line.product_name + (line.is_serialized ? '' : ' <span class="badge badge-light border text-muted">Non-serialized</span>') + '</td>' +
                        '<td class="text-right font-weight-bold">' + line.quantity_requested + '</td>' +
                        '<td class="text-right text-primary font-weight-bold">' + line.scanned_qty + '</td>' +
                        '<td class="text-right text-danger font-weight-bold">' + remaining + '</td>' +
                        '<td class="text-center"><span class="badge ' + badgeClass + ' px-2 py-1">' + badgeText + '</span></td>' +
                        '</tr>';
                });

                $('#modal-products-table tbody').html(html);

                // Enable submit only when all quantities matched
                if (allComplete && doLines.length > 0) {
                    $('#btn-submit-scans').prop('disabled', false);
                } else {
                    $('#btn-submit-scans').prop('disabled', true);
                }
            }

            function renderScannedList() {
                var html = '';
                scannedSerials.forEach(function(item, index) {
                    html += '<li class="list-group-item d-flex justify-content-between align-items-center py-2 bg-white border mb-1 rounded shadow-xs">' +
                        '<div>' +
                        '<span class="font-weight-bold text-dark mr-2">' + item.serial_number + '</span>' +
                        '<small class="text-muted">(' + item.product_name + ')</small>' +
                        '</div>' +
                        '<button type="button" class="btn btn-outline-danger btn-xs remove-serial" data-index="' + index + '">' +
                        '<i class="fa fa-trash"></i> Remove' +
                        '</button>' +
                        '</li>';
                });
                if (scannedSerials.length === 0) {
                    html = '<li class="list-group-item text-center text-muted py-3 bg-light">No serial numbers scanned yet.</li>';
                }
                $('#scanned-serials-list').html(html);
            }

            function showAlert(type, message) {
                var html = '<div class="alert alert-' + type + ' alert-dismissible fade show shadow-xs border-0" role="alert">' +
                    message +
                    '<button type="button" class="close text-' + type + '" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span>' +
                    '</button>' +
                    '</div>';
                $('#scan-alert-area').html(html);
                
                // Auto-close alert after 5 seconds
                setTimeout(function() {
                    $('.alert').alert('close');
                }, 5000);
            }
        });
    </script>
@endpush
