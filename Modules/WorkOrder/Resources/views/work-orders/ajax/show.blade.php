<div class="row">
    <div class="col-sm-12">
        <div class="bg-white rounded p-20">

            {{-- ── Header ─────────────────────────────────────────────────────── --}}
            <div class="d-flex justify-content-between align-items-center border-bottom-grey pb-3 mb-4">
                <div>
                    <h4 class="mb-0 f-21 font-weight-normal">{{ $workOrder->wo_number }}</h4>
                    <p class="text-lightest f-12 mb-0">{{ $workOrder->wo_date ? $workOrder->wo_date->format(company()->date_format) : '' }}</p>
                </div>
                <div class="d-flex">
                    @php
                        $statusClasses = ['draft'=>'badge-secondary','pending_approval'=>'badge-warning','approved'=>'badge-success','rejected'=>'badge-danger','in_progress'=>'badge-info','completed'=>'badge-primary','cancelled'=>'badge-dark'];
                        $cls = $statusClasses[$workOrder->status] ?? 'badge-secondary';
                    @endphp
                    <span class="badge {{ $cls }} f-14 px-3 py-2">{{ ucwords(str_replace('_',' ',$workOrder->status)) }}</span>
                </div>
            </div>

            {{-- ── Basic Info ──────────────────────────────────────────────────── --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <p class="mb-1 text-lightest f-12">@lang('workorder::modules.workOrder.event')</p>
                    <p class="mb-0 text-dark-grey f-14 font-weight-bold">{{ $workOrder->event->event_name ?? '--' }}</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-lightest f-12">@lang('workorder::modules.workOrder.vendor')</p>
                    <p class="mb-0 text-dark-grey f-14 font-weight-bold">{{ $workOrder->vendor->vendor_name ?? '--' }}</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-lightest f-12">@lang('workorder::modules.workOrder.venue')</p>
                    <p class="mb-0 text-dark-grey f-14">{{ $workOrder->venue ?? '--' }}</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-lightest f-12">@lang('workorder::modules.workOrder.deliveryDate')</p>
                    <p class="mb-0 text-dark-grey f-14">{{ $workOrder->delivery_date ? $workOrder->delivery_date->format(company()->date_format) : '--' }}</p>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <p class="mb-1 text-lightest f-12">@lang('workorder::modules.workOrder.workCategory')</p>
                    <p class="mb-0 text-dark-grey f-14">{{ $workOrder->work_category ?? '--' }}</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-lightest f-12">@lang('workorder::modules.workOrder.noOfDays')</p>
                    <p class="mb-0 text-dark-grey f-14">{{ $workOrder->no_of_days }}</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-lightest f-12">@lang('workorder::modules.workOrder.priority')</p>
                    <p class="mb-0 text-dark-grey f-14">{{ ucfirst($workOrder->priority) }}</p>
                </div>
                <div class="col-md-3">
                    <p class="mb-1 text-lightest f-12">@lang('workorder::modules.workOrder.createdBy')</p>
                    <p class="mb-0 text-dark-grey f-14">{{ $workOrder->creator->name ?? '--' }}</p>
                </div>
            </div>

            @if($workOrder->description)
            <div class="row mb-3">
                <div class="col-md-12">
                    <p class="mb-1 text-lightest f-12">@lang('workorder::modules.workOrder.description')</p>
                    <p class="mb-0 text-dark-grey f-14">{{ $workOrder->description }}</p>
                </div>
            </div>
            @endif

            {{-- ── Items Table ─────────────────────────────────────────────────── --}}
            <div class="border-top-grey pt-3 mt-3">
                <h5 class="f-15 f-w-500 mb-3">Items / Services</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm f-14">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>@lang('workorder::modules.workOrder.itemName')</th>
                                <th class="text-right">@lang('workorder::modules.workOrder.quantity')</th>
                                <th>@lang('workorder::modules.workOrder.unit')</th>
                                <th class="text-right">@lang('workorder::modules.workOrder.rate')</th>
                                <th>@lang('workorder::modules.workOrder.taxType')</th>
                                <th class="text-right">@lang('workorder::modules.workOrder.taxPercent')</th>
                                <th class="text-right">@lang('workorder::modules.workOrder.total')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workOrder->items as $i => $item)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $item->item_name }}</td>
                                <td class="text-right">{{ $item->quantity }}</td>
                                <td>{{ $item->unit ?? '--' }}</td>
                                <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                                <td>{{ ucfirst($item->tax_type) }}</td>
                                <td class="text-right">{{ $item->tax_percent }}%</td>
                                <td class="text-right font-weight-bold">{{ number_format($item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Totals ──────────────────────────────────────────────────────── --}}
            <div class="row justify-content-end mt-3">
                <div class="col-md-5">
                    <table class="table table-sm">
                        <tr><td>Sub Total</td><td class="text-right">{{ number_format($workOrder->sub_total, 2) }}</td></tr>
                        <tr><td>Discount</td><td class="text-right">{{ number_format($workOrder->discount, 2) }} {{ $workOrder->discount_type == 'percent' ? '%' : '' }}</td></tr>
                        <tr><td>Tax Amount</td><td class="text-right">{{ number_format($workOrder->tax_amount, 2) }}</td></tr>
                        <tr class="font-weight-bold f-15"><td>Grand Total</td><td class="text-right">{{ number_format($workOrder->grand_total, 2) }}</td></tr>
                    </table>
                </div>
            </div>

            {{-- ── Approval Actions ────────────────────────────────────────────── --}}
            @if($canApprove)
            <div class="border-top-grey pt-3 mt-3">
                <h5 class="f-15 f-w-500 mb-3">@lang('workorder::modules.workOrder.approvals')</h5>
                <div class="row">
                    <div class="col-md-8">
                        <x-forms.textarea fieldId="approval_remarks" fieldLabel="Remarks (required for Reject / Send Back)"
                            fieldName="approval_remarks" />
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <button type="button" class="btn btn-success mr-2" id="btn-approve">
                            <i class="fa fa-check mr-1"></i> @lang('workorder::modules.workOrder.approve')
                        </button>
                        <button type="button" class="btn btn-danger mr-2" id="btn-reject">
                            <i class="fa fa-times mr-1"></i> @lang('workorder::modules.workOrder.reject')
                        </button>
                        <button type="button" class="btn btn-warning" id="btn-send-back">
                            <i class="fa fa-undo mr-1"></i> @lang('workorder::modules.workOrder.sendBack')
                        </button>
                    </div>
                </div>
            </div>
            @endif

            {{-- ── Approval History ────────────────────────────────────────────── --}}
            @if($workOrder->approvals->count())
            <div class="border-top-grey pt-3 mt-3">
                <h5 class="f-15 f-w-500 mb-3">Approval History</h5>
                @foreach($workOrder->approvals as $log)
                <div class="d-flex mb-3">
                    <div class="mr-3">
                        <span class="badge badge-{{ in_array($log->action,['approved']) ? 'success' : (in_array($log->action,['rejected']) ? 'danger' : 'secondary') }} px-2 py-1">
                            {{ ucfirst($log->action) }}
                        </span>
                    </div>
                    <div>
                        <p class="mb-0 font-weight-bold f-14">{{ $log->user->name ?? '--' }}</p>
                        <p class="text-lightest f-12 mb-0">{{ $log->created_at->format(company()->date_format . ' H:i') }}</p>
                        @if($log->remarks) <p class="text-dark-grey f-13 mb-0">{{ $log->remarks }}</p> @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- ── Action Buttons ──────────────────────────────────────────────── --}}
            <div class="border-top-grey pt-3 mt-3 d-flex">
                <a href="{{ route('work-orders.pdf', $workOrder->id) }}" class="btn btn-secondary mr-2" target="_blank">
                    <i class="fa fa-file-pdf mr-1"></i> @lang('workorder::modules.workOrder.downloadPdf')
                </a>
                @if($workOrder->isRejected())
                <button type="button" class="btn btn-outline-primary mr-2" id="btn-duplicate">
                    <i class="fa fa-copy mr-1"></i> @lang('workorder::modules.workOrder.duplicate')
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    function approvalAction(url, remarks) {
        $.easyAjax({
            url: url,
            type: 'POST',
            data: { '_token': '{{ csrf_token() }}', remarks: remarks },
            success: function (response) {
                if (response.status === 'success') {
                    if ($(RIGHT_MODAL).hasClass('in')) {
                        document.getElementById('right-modal-content').innerHTML = '';
                        $(RIGHT_MODAL).modal('hide');
                    }
                    window.location.href = response.redirectUrl;
                }
            }
        });
    }

    $('#btn-approve').click(function () {
        approvalAction("{{ route('work-orders.approve', $workOrder->id) }}", $('#approval_remarks').val());
    });
    $('#btn-reject').click(function () {
        var r = $('#approval_remarks').val();
        if (!r) { alert('Remarks are required for rejection.'); return; }
        approvalAction("{{ route('work-orders.reject', $workOrder->id) }}", r);
    });
    $('#btn-send-back').click(function () {
        var r = $('#approval_remarks').val();
        if (!r) { alert('Remarks are required for send back.'); return; }
        approvalAction("{{ route('work-orders.send-back', $workOrder->id) }}", r);
    });
    $('#btn-duplicate').click(function () {
        $.easyAjax({
            url: "{{ route('work-orders.duplicate', $workOrder->id) }}",
            type: 'POST',
            data: { '_token': '{{ csrf_token() }}' },
            success: function (response) {
                if (response.status === 'success') { window.location.href = response.redirectUrl; }
            }
        });
    });

    init(RIGHT_MODAL);
});
</script>
