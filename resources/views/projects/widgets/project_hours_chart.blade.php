<!-- BUDGET VS SPENT START -->
<x-cards.data>
    <div class="row {{ $projectBudgetPermission == 'all' ? 'row-cols-lg-2' : '' }}">
        @if ($viewProjectTimelogPermission == 'all')
            <div class="col">
                <h4 class="f-18 f-w-500 mb-0">@lang('modules.projects.hoursLogged')</h4>
                <x-stacked-chart id="task-chart2" :chartData="$hoursBudgetChart" height="250" />
            </div>
        @endif
        @if ($projectBudgetPermission == 'all')
            <div class="col">
                <h4 class="f-18 f-w-500 mb-0">@lang('modules.projects.projectBudget')</h4>
                <x-stacked-chart id="task-chart3" :chartData="$amountBudgetChart" height="250" />
            </div>
        @endif
    </div>
</x-cards.data>
<!-- BUDGET VS SPENT END -->
