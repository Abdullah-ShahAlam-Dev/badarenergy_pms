@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        @include('dailyreports::ajax.show')
    </div>
@endsection
