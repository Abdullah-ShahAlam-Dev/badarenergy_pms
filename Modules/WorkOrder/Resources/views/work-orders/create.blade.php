{{-- Wrapper for direct URL access (non-AJAX) --}}
@extends('layouts.app')
@section('content')
<div class="content-wrapper">
    @include('workorder::work-orders.ajax.create')
</div>
@endsection
