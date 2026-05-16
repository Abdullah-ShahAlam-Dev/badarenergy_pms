@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex flex-column w-tables bg-white rounded">
            <div class="p-20">
                <h4 class="mb-4 f-21 font-weight-bold">@lang('gatepass::modules.gatePass.menuName')</h4>
                @include('gatepass::ajax.create')
            </div>
        </div>
    </div>
@endsection
