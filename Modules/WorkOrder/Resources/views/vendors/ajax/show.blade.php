<div class="row">
    <div class="col-sm-12">
        <div class="bg-white rounded p-20">
            <div class="d-flex justify-content-between align-items-center border-bottom-grey pb-3 mb-4">
                <div>
                    <h4 class="mb-0 f-21 font-weight-normal">{{ $vendor->vendor_name }}</h4>
                    <p class="text-lightest f-12 mb-0">{{ $vendor->company_name }}</p>
                </div>
                <span class="badge {{ $vendor->status == 'active' ? 'badge-success' : 'badge-danger' }} f-13 px-3 py-2">{{ ucfirst($vendor->status) }}</span>
            </div>

            <div class="row mb-4">
                <div class="col-md-4">
                    <p class="mb-1 text-lightest f-12">@lang('workorder::modules.vendor.designation')</p>
                    <p class="mb-0 text-dark-grey f-14">{{ $vendor->designation ?? '--' }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1 text-lightest f-12">@lang('workorder::modules.vendor.mobile')</p>
                    <p class="mb-0 text-dark-grey f-14">{{ $vendor->mobile ?? '--' }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1 text-lightest f-12">@lang('workorder::modules.vendor.alternateMobile')</p>
                    <p class="mb-0 text-dark-grey f-14">{{ $vendor->alternate_mobile ?? '--' }}</p>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-4">
                    <p class="mb-1 text-lightest f-12">@lang('workorder::modules.vendor.email')</p>
                    <p class="mb-0 text-dark-grey f-14">{{ $vendor->email ?? '--' }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1 text-lightest f-12">@lang('workorder::modules.vendor.cnic')</p>
                    <p class="mb-0 text-dark-grey f-14">{{ $vendor->cnic ?? '--' }}</p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1 text-lightest f-12">@lang('workorder::modules.vendor.ntn')</p>
                    <p class="mb-0 text-dark-grey f-14">{{ $vendor->ntn ?? '--' }}</p>
                </div>
            </div>
            @if($vendor->office_address)
            <div class="row mb-3">
                <div class="col-md-12">
                    <p class="mb-1 text-lightest f-12">@lang('workorder::modules.vendor.officeAddress')</p>
                    <p class="mb-0 text-dark-grey f-14">{{ $vendor->office_address }}</p>
                </div>
            </div>
            @endif
            @if($vendor->bank_details)
            <div class="row mb-3">
                <div class="col-md-12">
                    <p class="mb-1 text-lightest f-12">@lang('workorder::modules.vendor.bankDetails')</p>
                    <p class="mb-0 text-dark-grey f-14">{{ $vendor->bank_details }}</p>
                </div>
            </div>
            @endif

            {{-- Recent Work Orders --}}
            @if($workOrders->count())
            <div class="border-top-grey pt-3 mt-3">
                <h5 class="f-15 f-w-500 mb-3">@lang('workorder::modules.vendor.workOrderHistory')</h5>
                @foreach($workOrders as $wo)
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div>
                        <a href="{{ route('work-orders.show', $wo->id) }}" class="openRightModal text-darkest-grey font-weight-bold f-14">{{ $wo->wo_number }}</a>
                        <small class="text-lightest ml-2">{{ $wo->event->event_name ?? '' }}</small>
                    </div>
                    <span class="badge badge-{{ $wo->status == 'approved' ? 'success' : 'secondary' }}">{{ ucwords(str_replace('_',' ',$wo->status)) }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    init(RIGHT_MODAL);
});
</script>
