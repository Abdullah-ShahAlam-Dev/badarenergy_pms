@extends('layouts.app')

@section('content')

    <div class="content-wrapper">
        <div class="d-flex d-lg-none">
            <x-app-title :pageTitle="$pageTitle"></x-app-title>
        </div>

        <div class="p-20">
            @include($view)
        </div>
    </div>

@endsection
