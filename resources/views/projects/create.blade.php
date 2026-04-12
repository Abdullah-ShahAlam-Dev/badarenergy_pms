@extends('layouts.app')

@section('content')

    <div class="content-wrapper">
        @if(isset($pageTitle) && !request()->ajax())
            <div class="d-flex d-lg-none">
                <x-app-title :pageTitle="__($pageTitle)"></x-app-title>
            </div>
        @endif

        <div class="p-20">
            @include($view)
        </div>
    </div>

@endsection