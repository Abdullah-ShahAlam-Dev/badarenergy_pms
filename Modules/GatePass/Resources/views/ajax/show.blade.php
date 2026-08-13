<div class="row">
    <div class="col-sm-12">
        <div class="card border-0 b-shadow-4">
            <div class="card-header bg-white border-0 text-capitalize d-flex justify-content-between pt-4">
                <h4 class="f-18 f-w-500 mb-0">Gate Pass Detail: {{ $gatePass->request_number }}</h4>
                <div class="header-action">
                    @if($gatePass->status == 'pending_security' || $gatePass->status == 'completed')
                        <a href="{{ route('gate-pass.print', $gatePass->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm mr-2">
                            <i class="fa fa-print mr-1"></i> Print Pass
                        </a>
                    @endif
                    <span class="badge {{ $gatePass->status == 'completed' ? 'badge-success' : 'badge-warning' }} p-2">
                        {{ strtoupper(str_replace('_', ' ', $gatePass->status)) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <p class="mb-1 text-lightest f-12">Requested By</p>
                        <x-employee :user="$gatePass->user" />
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1 text-lightest f-12">Department</p>
                        <p class="mb-0 text-dark-grey f-14 font-weight-bold">{{ $gatePass->department->team_name ?? '--' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1 text-lightest f-12">Battery Approval Status</p>
                        @if($gatePass->requires_battery_approval)
                            <span class="badge badge-warning text-dark p-2"><i class="fa fa-battery-quarter mr-1"></i> Mandatory Manager Approval (Battery Parts)</span>
                        @else
                            <span class="badge badge-info p-2"><i class="fa fa-check-circle mr-1"></i> Auto-Approved (Non-Battery)</span>
                        @endif
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <p class="mb-1 text-lightest f-12">Date</p>
                        <p class="mb-0 text-dark-grey f-14">{{ $gatePass->request_date->format(company()->date_format) }}</p>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1 text-lightest f-12">Type</p>
                        <p class="mb-0 text-dark-grey f-14">{{ strtoupper($gatePass->type) }}</p>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1 text-lightest f-12">Return Type</p>
                        <p class="mb-0 text-dark-grey f-14">{{ ucwords($gatePass->return_type) }}</p>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1 text-lightest f-12">Expected Return</p>
                        <p class="mb-0 text-dark-grey f-14">{{ $gatePass->expected_return_date ? $gatePass->expected_return_date->format(company()->date_format) : '--' }}</p>
                    </div>
                </div>

                <div class="row mb-4 border-top-grey pt-3">
                    <div class="col-md-12">
                        <p class="mb-1 text-lightest f-12">Purpose / Reason</p>
                        <p class="mb-0 text-dark-grey f-14">{{ $gatePass->purpose }}</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-1 text-lightest f-12">Movement</p>
                        <p class="mb-0 text-dark-grey f-14">From: <strong>{{ $gatePass->from_location ?? '--' }}</strong> To: <strong>{{ $gatePass->to_location ?? '--' }}</strong></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-lightest f-12">Vehicle & Driver</p>
                        <p class="mb-0 text-dark-grey f-14">Vehicle: {{ $gatePass->vehicle_number ?? '--' }} | Driver: {{ $gatePass->driver_name ?? '--' }}</p>
                    </div>
                </div>

                <h5 class="f-16 f-w-500 mb-3 border-top-grey pt-3">Items Requested</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered f-14">
                        <thead class="bg-light">
                            <tr>
                                <th>Item Name</th>
                                <th>Quantity</th>
                                <th>Serial / Asset Tag</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gatePass->items as $item)
                                <tr>
                                    <td>{{ $item->item_name }}</td>
                                    <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                    <td>{{ $item->serial_number ?: ($item->asset_tag ?: '--') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Security Gate Exit Verification Stamp -->
                <div class="card bg-light border p-3 rounded mb-4">
                    <h6 class="f-15 f-w-500 mb-2"><i class="fa fa-shield text-primary mr-1"></i> Security Gate Exit Verification Stamp</h6>
                    @if($gatePass->exit_scanned_at)
                        <div class="alert alert-success mb-0 py-2">
                            <i class="fa fa-check-circle mr-1"></i> <strong>Exit Verified & Passed Gate:</strong>
                            Scanned by <strong>{{ $gatePass->exitScannedBy->name ?? 'Security Guard' }}</strong> on 
                            <strong>{{ $gatePass->exit_scanned_at->format('Y-m-d H:i:s') }}</strong>.
                        </div>
                    @else
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted f-13">Status: Pending Warehouse Exit Verification</span>
                            @if(in_array($gatePass->status, ['approved', 'pending_security']))
                                <x-form id="security-exit-scan-form" action="{{ route('gate-pass.security-exit-scan', $gatePass->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success rounded" onclick="return confirm('Verify warehouse exit for this Gate Pass?')">
                                        <i class="fa fa-barcode mr-1"></i> Verify & Stamp Exit Gate Pass
                                    </button>
                                </x-form>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Approval Section -->
                @if($gatePass->status == 'pending_hod' && user()->permission('approve_gate_pass') != 'none')
                    <div class="bg-light-grey p-20 rounded mb-4 border">
                        <h6 class="f-15 f-w-500 mb-3">HOD Action Required</h6>
                        <x-form id="hod-action-form">
                            <x-forms.textarea fieldId="hod_remarks" fieldLabel="HOD Remarks" fieldName="remarks" />
                            <div class="mt-3">
                                <button type="button" class="btn btn-primary hod-btn" data-action="approve">Approve</button>
                                <button type="button" class="btn btn-outline-secondary hod-btn" data-action="revision">Send Back</button>
                                <button type="button" class="btn btn-danger hod-btn" data-action="reject">Reject</button>
                            </div>
                        </x-form>
                    </div>
                @endif

                @if($gatePass->status == 'pending_store' && user()->permission('verify_gate_pass') != 'none')
                    <div class="bg-light-grey p-20 rounded mb-4 border">
                        <h6 class="f-15 f-w-500 mb-3">Store Verification Required</h6>
                        <x-form id="store-action-form">
                            <x-forms.textarea fieldId="store_remarks" fieldLabel="Store Remarks" fieldName="remarks" />
                            <div class="mt-3">
                                <button type="button" class="btn btn-primary store-btn" data-action="authorize">Authorize</button>
                                <button type="button" class="btn btn-outline-warning store-btn" data-action="send_back"><i class="fa fa-arrow-left mr-1"></i> Send Back to HOD</button>
                                <button type="button" class="btn btn-outline-secondary store-btn" data-action="hold">Hold</button>
                                <button type="button" class="btn btn-danger store-btn" data-action="reject">Reject</button>
                            </div>
                        </x-form>
                    </div>
                @endif

                @if($gatePass->status == 'pending_security' && user()->permission('authorize_gate_pass') != 'none')
                    <div class="bg-light-grey p-20 rounded mb-4 border">
                        <h6 class="f-15 f-w-500 mb-3">Security Clearance Required</h6>
                        <div class="text-center mb-3">
                            <!-- QR Verification -->
                            <div class="p-3 bg-white d-inline-block border">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('gate-pass.verify', $gatePass->qr_code)) }}" alt="QR Code">
                            </div>
                            <p class="mt-2 f-12 text-lightest">Scan this QR code at the gate</p>
                        </div>
                        <x-form id="security-action-form">
                            <x-forms.textarea fieldId="security_remarks" fieldLabel="Security Remarks" fieldName="remarks" />
                            <div class="mt-3 text-center">
                                <button type="button" class="btn btn-success security-btn btn-lg" data-action="allow">
                                    <i class="fa fa-check mr-1"></i> ALLOW EXIT
                                </button>
                                <button type="button" class="btn btn-outline-warning security-btn btn-lg ml-2" data-action="send_back">
                                    <i class="fa fa-arrow-left mr-1"></i> Send Back to Store
                                </button>
                                <button type="button" class="btn btn-outline-danger security-btn btn-lg ml-2" data-action="reject">REJECT EXIT</button>
                            </div>
                        </x-form>
                    </div>
                @endif

                @if($gatePass->status == 'open' && user()->permission('verify_gate_pass') != 'none')
                    <div class="bg-light-grey p-20 rounded mb-4 border border-info">
                        <h6 class="f-15 f-w-500 mb-3 text-dark"><i class="fa fa-exchange-alt mr-1 text-info"></i> Record Item Returns / Settlements</h6>
                        <x-form id="record-return-form">
                            <div class="table-responsive">
                                <table class="table table-bordered f-14 bg-white">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Item Name</th>
                                            <th>Qty Requested</th>
                                            <th>Qty Returned</th>
                                            <th>Qty Settled</th>
                                            <th>Qty Pending</th>
                                            <th style="width: 100px;">Return Qty</th>
                                            <th style="width: 100px;">Settle Qty</th>
                                            <th>Settle Status</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($gatePass->items as $item)
                                            @php
                                                $pending = $item->quantity - $item->returned_quantity - $item->settled_quantity;
                                            @endphp
                                            <tr>
                                                <td class="font-weight-bold">
                                                    {{ $item->item_name }}
                                                    <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                                                </td>
                                                <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                                <td class="text-success">{{ $item->returned_quantity }} {{ $item->unit }}</td>
                                                <td class="text-info">{{ $item->settled_quantity }} {{ $item->unit }}</td>
                                                <td class="font-weight-bold text-danger">{{ $pending }} {{ $item->unit }}</td>
                                                <td>
                                                    @if($pending > 0)
                                                        <input type="number" class="form-control height-30 f-14 p-1" name="return_qty[{{ $item->id }}]" min="0" max="{{ $pending }}" step="0.01" value="0">
                                                    @else
                                                        <span class="text-muted">Completed</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($pending > 0)
                                                        <input type="number" class="form-control height-30 f-14 p-1" name="settle_qty[{{ $item->id }}]" min="0" max="{{ $pending }}" step="0.01" value="0">
                                                    @else
                                                        <span class="text-muted">Completed</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($pending > 0)
                                                        <select class="form-control height-30 f-14 p-1" name="settle_status[{{ $item->id }}]">
                                                            <option value="returned">Returned</option>
                                                            <option value="damaged">Damaged</option>
                                                            <option value="lost">Lost</option>
                                                            <option value="consumed">Consumed</option>
                                                            <option value="unreturnable">Unreturnable</option>
                                                        </select>
                                                    @else
                                                        --
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($pending > 0)
                                                        <input type="text" class="form-control height-30 f-14 p-1" name="remarks[{{ $item->id }}]" placeholder="Remarks">
                                                    @else
                                                        --
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-primary" id="save-return-btn">
                                    <i class="fa fa-save mr-1"></i> Save Returns
                                </button>
                                
                                <button type="button" class="btn btn-outline-danger" data-toggle="collapse" data-target="#manual-close-section">
                                    <i class="fa fa-times mr-1"></i> Manually Close Gate Pass
                                </button>
                            </div>
                        </x-form>

                        <div class="collapse mt-3" id="manual-close-section">
                            <div class="card card-body bg-white border">
                                <h6 class="f-14 font-weight-bold text-danger mb-2">Manually Close Document</h6>
                                <p class="f-12 text-muted mb-3">This will mark all remaining pending items as unreturnable and close the gate pass request. This action is irreversible.</p>
                                <x-form id="manual-close-form">
                                    <x-forms.textarea fieldId="manual_close_remarks" fieldLabel="Closure Reason / Remarks" fieldName="manual_close_remarks" fieldRequired="true" />
                                    <button type="button" class="btn btn-danger btn-sm mt-3" id="confirm-manual-close">
                                        Confirm Close Gate Pass
                                    </button>
                                </x-form>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Returns Log Section -->
                @if($gatePass->returns->count() > 0)
                    <h5 class="f-16 f-w-500 mb-3 border-top-grey pt-3 text-dark"><i class="fa fa-history mr-1 text-info"></i> Returned & Settled Items History</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-striped f-14 bg-white">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Item Name</th>
                                    <th>Quantity</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Recorded By</th>
                                    <th>Remarks / Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gatePass->returns as $return)
                                    <tr>
                                        <td>{{ $return->created_at->format(company()->date_format . ' H:i') }}</td>
                                        <td>{{ $return->item->item_name ?? 'Unknown' }}</td>
                                        <td>{{ $return->quantity }} {{ $return->item->unit ?? '' }}</td>
                                        <td>
                                            <span class="badge {{ $return->type == 'return' ? 'badge-success' : 'badge-info' }} p-1 text-uppercase">
                                                {{ $return->type }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-light p-1 text-uppercase">
                                                {{ $return->status }}
                                            </span>
                                        </td>
                                        <td>{{ $return->user->name ?? 'System' }}</td>
                                        <td>{{ $return->remarks ?: '--' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Timeline / Logs -->
                <h5 class="f-16 f-w-500 mb-3 border-top-grey pt-3">Approval History</h5>
                <div class="timeline f-14">
                    @foreach($gatePass->logs as $log)
                        <div class="d-flex mb-3">
                            <div class="mr-3">
                                <span class="badge badge-light p-2">{{ $log->created_at->format('H:i') }}</span>
                            </div>
                            <div>
                                <p class="mb-0 font-weight-bold text-dark-grey">{{ strtoupper(str_replace('_', ' ', $log->action)) }}</p>
                                <p class="mb-1 text-lightest f-12">by {{ $log->user->name }} on {{ $log->created_at->format(company()->date_format) }}</p>
                                @if($log->remarks)
                                    <p class="mb-0 bg-light p-2 rounded italic f-13 text-darkest-grey">"{{ $log->remarks }}"</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.hod-btn').click(function() {
            var action = $(this).data('action');
            var remarks = $('#hod_remarks').val();
            var url = "{{ route('gate-pass.hod-action', $gatePass->id) }}";
            submitAction(url, action, remarks);
        });

        $('.store-btn').click(function() {
            var action = $(this).data('action');
            var remarks = $('#store_remarks').val();
            var url = "{{ route('gate-pass.store-action', $gatePass->id) }}";
            submitAction(url, action, remarks);
        });

        $('.security-btn').click(function() {
            var action = $(this).data('action');
            var remarks = $('#security_remarks').val();
            var url = "{{ route('gate-pass.security-action', $gatePass->id) }}";
            submitAction(url, action, remarks);
        });

        $('#save-return-btn').click(function() {
            var url = "{{ route('gate-pass.record-return', $gatePass->id) }}";
            $.easyAjax({
                url: url,
                container: '#record-return-form',
                type: "POST",
                disableButton: true,
                buttonSelector: "#save-return-btn",
                data: $('#record-return-form').serialize() + '&_token=' + "{{ csrf_token() }}",
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.reload();
                    }
                }
            });
        });

        $('#confirm-manual-close').click(function() {
            var url = "{{ route('gate-pass.manually-close', $gatePass->id) }}";
            var remarks = $('#manual_close_remarks').val();
            if (!remarks) {
                alert('Please enter closure remarks.');
                return;
            }
            $.easyAjax({
                url: url,
                container: '#manual-close-form',
                type: "POST",
                disableButton: true,
                buttonSelector: "#confirm-manual-close",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'manual_close_remarks': remarks
                },
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.reload();
                    }
                }
            });
        });

        function submitAction(url, action, remarks) {
            $.easyAjax({
                url: url,
                type: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'action': action,
                    'remarks': remarks
                },
                success: function(response) {
                    if (response.status == 'success') {
                        if ($(RIGHT_MODAL).hasClass('in')) {
                            document.getElementById('right-modal-content').innerHTML = "";
                            $(RIGHT_MODAL).modal('hide');
                        }
                        window.location.reload();
                    }
                }
            });
        }
    });
</script>
