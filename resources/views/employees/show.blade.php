@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
$viewEmployeeTasks = user()->permission('view_employee_tasks');
$viewTickets = user()->permission('view_tickets');
$viewEmployeeProjects = user()->permission('view_employee_projects');
$viewEmployeeTimelogs = user()->permission('view_employee_timelogs');
$manageEmergencyContact = user()->permission('manage_emergency_contact');
$manageRolePermissionSetting = user()->permission('manage_role_permission_setting');
$manageShiftPermission = user()->permission('view_shift_roster');
$viewLeavePermission = user()->permission('view_leave');
$viewDocumentPermission = user()->permission('view_documents');
$viewAppreciationPermission = user()->permission('view_appreciation');
$viewImmigrationPermission = user()->permission('view_immigration');
@endphp

@php

$showFullProfile = false;

if ($viewPermission == 'all'
    || ($viewPermission == 'added' && $employee->employeeDetail->added_by == user()->id)
    || ($viewPermission == 'owned' && $employee->employeeDetail->user_id == user()->id)
    || ($viewPermission == 'both' && ($employee->employeeDetail->user_id == user()->id || $employee->employeeDetail->added_by == user()->id))
) {
    $showFullProfile = true;
}

@endphp

@section('filter-section')
    <!-- FILTER START -->
    <!-- PROJECT HEADER START -->

    <div class="d-flex d-lg-block filter-box project-header bg-white">
        <div class="mobile-close-overlay w-100 h-100" id="close-client-overlay"></div>

        <div class="project-menu" id="mob-client-detail">
            <a class="d-none close-it" href="javascript:;" id="close-client-detail">
                <i class="fa fa-times"></i>
            </a>

            <nav class="tabs">
                <ul class="-primary">
                    <li>
                        <x-tab :href="route('employees.show', $employee->id)" :text="__('modules.employees.profile')" class="profile" />
                    </li>

                    @if ($viewEmployeeProjects == 'all' && in_array('projects', user_modules()))
                        <li>
                            <x-tab :href="route('employees.show', $employee->id) . '?tab=projects'" :text="__('app.menu.projects')" ajax="false" class="projects" />
                        </li>
                    @endif

                    @if ($viewEmployeeTasks == 'all' && in_array('tasks', user_modules()))
                        <li>
                            <x-tab :href="route('employees.show', $employee->id) . '?tab=tasks'" :text="__('app.menu.tasks')" ajax="false" class="tasks" />
                        </li>
                    @endif

                    @if (in_array('leaves', user_modules()) && ($viewLeavePermission == 'all' || ($viewLeavePermission == 'owned' || $viewLeavePermission == 'both') && $employee->id == user()->id ))
                        <li>
                            <x-tab :href="route('employees.show', $employee->id) . '?tab=leaves'" :text="__('app.menu.leaves')" ajax="false" class="leaves" />
                        </li>

                    <li>
                        <x-tab :href="route('employees.show', $employee->id) . '?tab=leaves-quota'" :text="__('app.menu.leavesQuota')" class="leaves-quota" />
                    </li>
                    @endif

                    @if ($viewEmployeeTimelogs == 'all')
                        <li>
                            <x-tab :href="route('employees.show', $employee->id) . '?tab=timelogs'" :text="__('app.menu.timeLogs')" ajax="false" class="timelogs" />
                        </li>
                    @endif

                    @if ($viewDocumentPermission != 'none')
                        <li>
                            <x-tab :href="route('employees.show', $employee->id) . '?tab=documents'" :text="__('app.menu.documents')" class="documents" />
                        </li>
                    @endif

                    @if ($showFullProfile && ($manageEmergencyContact == 'all' || $employee->id == user()->id))
                        <li>
                            <x-tab :href="route('employees.show', $employee->id) . '?tab=emergency-contacts'" :text="__('modules.emergencyContact.emergencyContact')" class="emergency-contacts" />
                        </li>
                    @endif

                    @if ($viewTickets == 'all')
                        <li>
                            <x-tab :href="route('employees.show', $employee->id) . '?tab=tickets'" :text="__('modules.tickets.ticket')" ajax="false" class="tickets" />
                        </li>
                    @endif

                    @if ($showFullProfile && !in_array('client', user_roles()))
                        <li>
                            <x-tab :href="route('employees.show', $employee->id) . '?tab=appreciation'" :text="__('app.menu.appreciation')" class="appreciation" />
                        </li>
                    @endif

                    @if ($manageShiftPermission == 'all')
                        <li>
                            <x-tab :href="route('employees.show', $employee->id) . '?tab=shifts'" :text="__('app.menu.shiftRoster')" class="shifts" />
                        </li>
                    @endif

                    @if ($manageRolePermissionSetting == 'all')
                        <li>
                            <x-tab :href="route('employees.show', $employee->id) . '?tab=permissions'" :text="__('modules.permission.permissions')" class="permissions" />
                        </li>
                    @endif

                    @if ($showFullProfile)
                        <li>
                            <x-tab :href="route('employees.show', $employee->id) . '?tab=activity'" :text="__('modules.employees.activity')" class="activity" />
                        </li>
                    @endif

                    @if($viewImmigrationPermission == 'all' ||  (in_array($viewImmigrationPermission, ['added', 'owned', 'both']) && user()->id == $employee->id))
                        <li>
                            <x-tab :href="route('employees.show', $employee->id) . '?tab=immigration'" :text="__('modules.employees.immigration')" class="immigration" />
                        </li>
                    @endif
                </ul>
            </nav>
        </div>

        <a class="mb-0 d-block d-lg-none text-dark-grey ml-auto mr-2 border-left-grey" onclick="openClientDetailSidebar()"><i class="fa fa-ellipsis-v "></i></a>
    </div>

    <!-- PROJECT HEADER END -->
@endsection

@section('content')
    <div class="content-wrapper pt-0 border-top-0 client-detail-wrapper">
        @include($view)
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            var $body = $('body');
            var namespace = '.employeesShow';
            var activeTab = "{{ $activeTab }}";

            $('.project-menu .' + activeTab).addClass('active');

            $body.off(namespace);

            $body.on('click' + namespace, '.project-menu .ajax-tab', function(event) {
                event.preventDefault();
                $('.project-menu .p-sub-menu').removeClass('active');
                $(this).addClass('active');

                var requestUrl = this.href;
                $.easyAjax({
                    url: requestUrl,
                    blockUI: true,
                    container: ".content-wrapper",
                    historyPush: true,
                    success: function(response) {
                        if (response.status == "success") {
                            $('.content-wrapper').html(response.html);
                            init('.content-wrapper');
                        }
                    }
                });
            });

            window.openClientDetailSidebar = function() {
                $('#mob-client-detail').addClass('open');
                $('#close-client-overlay').addClass('open');
            };

            $body.on('click' + namespace, '#close-client-detail, #close-client-overlay', function() {
                $('#mob-client-detail').removeClass('open');
                $('#close-client-overlay').removeClass('open');
            });

            /*******************************************************
                     More btn in projects menu Start
            *******************************************************/
            var container = document.querySelector('.tabs');
            if (container) {
                var primary = container.querySelector('.-primary');
                var primaryItems = container.querySelectorAll('.-primary > li:not(.-more)');
                container.classList.add('--jsfied');

                primary.insertAdjacentHTML('beforeend', `
                <li class="-more">
                    <button type="button" class="px-4 h-100 bg-grey d-none d-lg-flex align-items-center" aria-haspopup="true" aria-expanded="false">
                    {{__('app.more')}} <span>&darr;</span>
                    </button>
                    <ul class="-secondary" id="hide-project-menues">
                    ${primary.innerHTML}
                    </ul>
                </li>
                `);
                var secondary = container.querySelector('.-secondary');
                var secondaryItems = secondary.querySelectorAll('li');
                var allItems = container.querySelectorAll('li');
                var moreLi = primary.querySelector('.-more');
                var moreBtn = moreLi.querySelector('button');

                moreBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    container.classList.toggle('--show-secondary');
                    moreBtn.setAttribute('aria-expanded', container.classList.contains('--show-secondary'));
                });

                var doAdapt = function() {
                    allItems.forEach(function(item) { item.classList.remove('--hidden'); });
                    var stopWidth = moreBtn.offsetWidth;
                    var hiddenItems = [];
                    var primaryWidth = primary.offsetWidth;
                    primaryItems.forEach(function(item, i) {
                        if (primaryWidth >= stopWidth + item.offsetWidth) {
                            stopWidth += item.offsetWidth;
                        } else {
                            item.classList.add('--hidden');
                            hiddenItems.push(i);
                        }
                    });
                    if (!hiddenItems.length) {
                        moreLi.classList.add('--hidden');
                        container.classList.remove('--show-secondary');
                        moreBtn.setAttribute('aria-expanded', false);
                    } else {
                        secondaryItems.forEach(function(item, i) {
                            if (!hiddenItems.includes(i)) { item.classList.add('--hidden'); }
                        });
                    }
                };

                doAdapt();
                window.addEventListener('resize', doAdapt);

                document.addEventListener('click', function outsideClick(e) {
                    var el = e.target;
                    while (el) {
                        if (el === secondary || el === moreBtn) { return; }
                        el = el.parentNode;
                    }
                    container.classList.remove('--show-secondary');
                    moreBtn.setAttribute('aria-expanded', false);
                });

                document.addEventListener("turbo:before-cache", function cleanup() {
                    $body.off(namespace);
                    window.removeEventListener('resize', doAdapt);
                    delete window.openClientDetailSidebar;
                    document.removeEventListener("turbo:before-cache", cleanup);
                }, { once: true });
            } else {
                document.addEventListener("turbo:before-cache", function cleanup() {
                    $body.off(namespace);
                    delete window.openClientDetailSidebar;
                    document.removeEventListener("turbo:before-cache", cleanup);
                }, { once: true });
            }
            /*******************************************************
                     More btn in projects menu End
            *******************************************************/
        })();
    </script>
@endpush
