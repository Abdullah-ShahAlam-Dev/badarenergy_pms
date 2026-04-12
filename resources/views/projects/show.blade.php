@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
$viewProjectMemberPermission = user()->permission('view_project_members');
$viewProjectMilestonePermission = ($project->project_admin == user()->id) ? 'all' : user()->permission('view_project_milestones');
$viewTasksPermission = ($project->project_admin == user()->id) ? 'all' : user()->permission('view_project_tasks');
$viewGanttPermission = ($project->project_admin == user()->id) ? 'all' : user()->permission('view_project_gantt_chart');
$viewInvoicePermission = user()->permission('view_invoices');
$viewDiscussionPermission = user()->permission('view_project_discussions');
$viewNotePermission = user()->permission('view_project_note');
$viewFilesPermission = user()->permission('view_project_files');
$viewRatingPermission = user()->permission('view_project_rating');
$viewProjectTimelogPermission = user()->permission('view_timelogs');
$viewExpensePermission = user()->permission('view_expenses');
$viewMiroboardPermission = user()->permission('view_miroboard');
$viewPaymentPermission = user()->permission('view_payments');
$viewBurndownChartPermission = user()->permission('view_burndown_chart');
$projectArchived = $project->trashed();
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
                        <x-tab :href="route('projects.show', $project->id)" :text="__('modules.projects.overview')" class="overview" />
                    </li>

                    @if (
                        !$project->public && $viewProjectMemberPermission == 'all'
                    )
                        <li>
                            <x-tab :href="route('projects.show', $project->id).'?tab=members'" :text="__('modules.projects.members')"
                            class="members" />
                        </li>
                    @endif

                    @if ($viewFilesPermission == 'all' || ($viewFilesPermission == 'added' && user()->id == $project->added_by) || ($viewFilesPermission == 'owned' && user()->id == $project->client_id))
                        <li>
                            <x-tab :href="route('projects.show', $project->id).'?tab=files'" :text="__('modules.projects.files')"
                            class="files" />
                        </li>
                    @endif

                    @if ($viewProjectMilestonePermission == 'all' || $viewProjectMilestonePermission == 'added' || ($viewProjectMilestonePermission == 'owned' && user()->id == $project->client_id))
                        <li>
                            <x-tab :href="route('projects.show', $project->id).'?tab=milestones'"
                            :text="__('modules.projects.milestones')" class="milestones" />
                        </li>
                    @endif

                    @if (in_array('tasks', user_modules()) && ($viewTasksPermission == 'all' || ($viewTasksPermission == 'added' && user()->id == $project->added_by) || ($viewTasksPermission == 'owned' && user()->id == $project->client_id)))
                        <li>
                            <x-tab :href="route('projects.show', $project->id).'?tab=tasks'" :text="__('app.menu.tasks')" class="tasks"
                            ajax="false" />
                        </li>

                        @if (!$projectArchived)
                            <li>
                                <x-tab :href="route('projects.show', $project->id).'?tab=taskboard'" :text="__('modules.tasks.taskBoard')" class="taskboard" ajax="false" />
                            </li>

                            @if ($viewGanttPermission == 'all' || ($viewGanttPermission == 'added' && user()->id == $project->added_by) || ($viewGanttPermission == 'owned' && user()->id == $project->client_id))
                                <li>
                                    <x-tab :href="route('projects.show', $project->id).'?tab=gantt'" :text="__('modules.projects.viewGanttChart')" class="gantt" />
                                </li>
                            @endif
                        @endif
                    @endif

                    @if (in_array('invoices', user_modules()) && !is_null($project->client_id) && ($viewInvoicePermission == 'all' || ($viewInvoicePermission == 'added' && user()->id == $project->added_by) || ($viewInvoicePermission == 'owned' && user()->id == $project->client_id)))
                        <li>
                            <x-tab :href="route('projects.show', $project->id).'?tab=invoices'" :text="__('app.menu.invoices')" class="invoices" ajax="false" />
                        </li>
                    @endif

                    @if (in_array('timelogs', user_modules()) && ($viewProjectTimelogPermission == 'all' || ($viewProjectTimelogPermission == 'added' && user()->id == $project->added_by) || ($viewProjectTimelogPermission == 'owned' && user()->id == $project->client_id)))
                        <li>
                            <x-tab :href="route('projects.show', $project->id).'?tab=timelogs'" :text="__('app.menu.timeLogs')" class="timelogs" ajax="false" />
                        </li>
                    @endif

                    @if (in_array('expenses', user_modules()) && ($viewExpensePermission == 'all' || ($viewExpensePermission == 'added' && user()->id == $project->added_by) || ($viewExpensePermission == 'owned' && user()->id == $project->client_id)))
                        <li>
                            <x-tab :href="route('projects.show', $project->id).'?tab=expenses'" :text="__('app.menu.expenses')" class="expenses" ajax="false" />
                        </li>
                    @endif

                    @if ($viewMiroboardPermission == 'all' && $project->enable_miroboard &&
                    ((in_array('client', user_roles()) && $project->client_access && $project->client_id == user()->id)
                    || !in_array('client', user_roles()))
                    )
                        <li>
                            <x-tab :href="route('projects.show', $project->id).'?tab=miroboard'" :text="__('app.menu.miroboard')" class="miroboard" ajax="false" />
                        </li>
                    @endif

                    @if (in_array('payments', user_modules()) && !is_null($project->client_id) && ($viewPaymentPermission == 'all' || ($viewPaymentPermission == 'added' && user()->id == $project->added_by) || ($viewPaymentPermission == 'owned' && user()->id == $project->client_id)))
                        <li>
                            <x-tab :href="route('projects.show', $project->id).'?tab=payments'" :text="__('app.menu.payments')" class="payments" ajax="false" />
                        </li>
                    @endif

                    @if ($viewDiscussionPermission == 'all' || ($viewDiscussionPermission == 'added' && user()->id == $project->added_by) || ($viewDiscussionPermission == 'owned' && user()->id == $project->client_id))
                        <li>
                            <x-tab :href="route('projects.show', $project->id).'?tab=discussion'" :text="__('modules.projects.discussion')" class="discussion" ajax="false" />
                        </li>
                    @endif

                    @if ($viewNotePermission != 'none' )
                        <li>
                            <x-tab :href="route('projects.show', $project->id).'?tab=notes'" :text="__('modules.projects.note')" class="notes" ajax="false" />
                        </li>
                    @endif

                    @if ($viewRatingPermission != 'none' && !is_null($project->client_id))
                        <li>
                            <x-tab :href="route('projects.show', $project->id).'?tab=rating'" :text="__('modules.projects.rating')" class="rating" ajax="false" />
                        </li>
                    @endif

                    @if($viewBurndownChartPermission != 'none' || $project->project_admin == user()->id)
                        <li>
                            <x-tab :href="route('projects.show', $project->id).'?tab=burndown-chart'"
                                :text="__('modules.projects.burndownChart')" class="burndown-chart" ajax="false" />
                        </li>
                    @endif

                    <li>
                        <x-tab :href="route('projects.show', $project->id).'?tab=activity'"
                            :text="__('modules.employees.activity')" class="activity" />
                    </li>

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
            var namespace = '.projectShow';

            // Cleanup previous listeners if any
            $body.off(namespace);
            $(document).off(namespace);

            // AJAX Tab Loading
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
                            if (typeof init === 'function') init('.content-wrapper');
                        }
                    }
                });
            });

            var activeTab = "{{ $activeTab }}";
            $('.project-menu .' + activeTab).addClass('active');

            // More Button Adaptivity Logic
            var container = document.querySelector('.tabs');
            if (container && !container.classList.contains('--jsfied')) {
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

                var toggleSecondary = function(e) {
                    e.preventDefault();
                    container.classList.toggle('--show-secondary');
                    moreBtn.setAttribute('aria-expanded', container.classList.contains('--show-secondary'));
                };

                moreBtn.addEventListener('click', toggleSecondary);

                var doAdapt = function() {
                    if (!container) return;
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
                        moreLi.classList.remove('--hidden');
                        secondaryItems.forEach(function(item, i) {
                            if (!hiddenItems.includes(i)) {
                                item.classList.add('--hidden');
                            }
                        });
                    }
                };

                doAdapt();
                window.addEventListener('resize', doAdapt);

                var outsideClick = function(e) {
                    var el = e.target;
                    while (el) {
                        if (el === secondary || el === moreBtn) return;
                        el = el.parentNode;
                    }
                    container.classList.remove('--show-secondary');
                    moreBtn.setAttribute('aria-expanded', false);
                };
                $(document).on('click' + namespace, outsideClick);

                // Combined Cleanup
                document.addEventListener("turbo:before-cache", function cleanup() {
                    $body.off(namespace);
                    $(document).off(namespace);
                    window.removeEventListener('resize', doAdapt);
                    document.removeEventListener("turbo:before-cache", cleanup);
                }, { once: true });
            }
        })();
    </script>
@endpush
