@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between action-bar">
            <x-forms.link-secondary :link="route('shipments.index')" class="mb-2" icon="arrow-left">
                Back to Shipments
            </x-forms.link-secondary>
        </div>

        <!-- Detail Box Start -->
        <div class="mt-3">
            @include('shipments.ajax.show')
        </div>
    </div>
@endsection
