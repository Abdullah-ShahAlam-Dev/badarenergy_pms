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
                    <div class="col-md-6">
                        <p class="mb-1 text-lightest f-12">Requested By</p>
                        <x-employee :user="$gatePass->user" />
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-lightest f-12">Department</p>
                        <p class="mb-0 text-dark-grey f-14 font-weight-bold">{{ $gatePass->department->team_name ?? '--' }}</p>
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
                                <button type="button" class="btn btn-outline-danger security-btn ml-2" data-action="reject">REJECT EXIT</button>
                            </div>
                        </x-form>
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
