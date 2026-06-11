@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex flex-column w-tables bg-white rounded">
            <div class="p-20">
                <h4 class="mb-4 f-21 font-weight-bold">Add Email Segment</h4>
                @include('crmemail::segments.ajax.create')
            </div>
        </div>
    </div>
@endsection
