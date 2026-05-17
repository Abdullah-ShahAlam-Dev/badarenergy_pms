@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    @include('workorder::work-orders.ajax.show')
</div>
@endsection
