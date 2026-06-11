@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <x-setting-header :heading="__('CRM Email Settings')" />

        <div class="row">
            {{-- LEFT: Tab Navigation --}}
            <div class="col-xl-3 col-lg-4 col-md-12 mb-3">
                <div class="card">
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush setting-sidebar-list" id="crm-email-setting-tab">

                            <a href="{{ route('crm-email-settings.index') . '?tab=general' }}"
                               class="list-group-item list-group-item-action {{ $activeTab === 'general' ? 'active' : '' }}"
                               data-tab-target="#general-panel">
                                <i class="fa fa-cog mr-2"></i> @lang('General Settings')
                            </a>

                            <a href="{{ route('crm-email-settings.index') . '?tab=email' }}"
                               class="list-group-item list-group-item-action {{ $activeTab === 'email' ? 'active' : '' }}"
                               data-tab-target="#email-panel">
                                <i class="fa fa-envelope mr-2"></i> @lang('Email & Sending')
                            </a>

                        </ul>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Tab Content --}}
            <div class="col-xl-9 col-lg-8 col-md-12" id="crm-email-setting-content">
                @include($view)
            </div>
        </div>
    </div>
@endsection
