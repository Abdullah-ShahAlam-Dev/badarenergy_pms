@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between action-bar mb-3">
        <div id="table-actions" class="d-flex align-items-center">
            <x-forms.link-secondary :link="route('work-orders.index')" class="mr-3" icon="file-contract">
                @lang('workorder::modules.workOrder.menuName')
            </x-forms.link-secondary>

            <x-forms.link-secondary :link="route('vendors.index')" class="mr-3" icon="store">
                @lang('workorder::modules.vendor.vendors')
            </x-forms.link-secondary>
        </div>
    </div>

    @include('workorder::approval-mappings.ajax.index')
</div>
@endsection
