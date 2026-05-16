@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex flex-column w-tables bg-white rounded">
            <div class="p-20">
                <h4 class="mb-4 f-21 font-weight-bold">@lang('gatepass::modules.gatePass.menuName') Report</h4>
                
                <div class="row mb-4">
                    <div class="col-md-3">
                        <x-forms.datepicker fieldId="startDate" :fieldLabel="__('app.startDate')" fieldName="startDate"
                            :fieldPlaceholder="__('placeholders.date')" :fieldValue="$startDate" />
                    </div>
                    <div class="col-md-3">
                        <x-forms.datepicker fieldId="endDate" :fieldLabel="__('app.endDate')" fieldName="endDate"
                            :fieldPlaceholder="__('placeholders.date')" :fieldValue="$endDate" />
                    </div>
                    <div class="col-md-3">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                            <option value="all">All</option>
                            <option value="pending_hod">Pending HOD</option>
                            <option value="pending_store">Pending Store</option>
                            <option value="pending_security">Pending Security</option>
                            <option value="completed">Completed</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-3 pt-4">
                        <x-forms.button-primary id="filter-report" class="mt-2">Filter</x-forms.button-primary>
                    </div>
                </div>

                <div id="report-content">
                    @include('gatepass::reports.ajax.index')
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $('#filter-report').click(function() {
        var startDate = $('#startDate').val();
        var endDate = $('#endDate').val();
        var status = $('#status').val();
        
        var url = "{{ route('gate-pass.report') }}";
        
        $.easyAjax({
            url: url,
            type: "GET",
            data: { startDate: startDate, endDate: endDate, status: status },
            success: function(response) {
                if (response.status == 'success') {
                    $('#report-content').html(response.html);
                }
            }
        });
    });
</script>
@endpush
