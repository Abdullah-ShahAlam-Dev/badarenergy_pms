<!-- BUDGET VS SPENT START -->
<div class="row mb-4">
    <div class="col-sm-12">
        <h4 class="f-18 f-w-500 mb-4">@lang('app.statistics')</h4>
    </div>
    @if ($projectBudgetPermission == 'all')
        <div class="col">
            <x-cards.widget :title="__('modules.projects.projectBudget')"
                :value="((!is_null($project->project_budget) && $project->currency) ? currency_format($project->project_budget, $project->currency->id) : '0')"
                icon="coins" />
        </div>
    @endif

    @if ($viewPaymentPermission == 'all')
        <div class="col">
            <x-cards.widget :title="__('app.earnings')"
                :value="(!is_null($project->currency) ? currency_format($earnings, $project->currency->id) : currency_format($earnings))"
                icon="coins" />
        </div>
    @endif
</div>
<div class="row">
    @if ($viewProjectTimelogPermission == 'all')
        <div class="col">
            <x-cards.widget :title="__('modules.projects.hoursLogged')" :value="$hoursLogged"
                icon="clock" />
        </div>
    @endif

    @if ($viewExpensePermission == 'all')
        <div class="col">
            <x-cards.widget :title="__('modules.projects.expenses_total')"
                :value="(!is_null($project->currency) ? currency_format($expenses, $project->currency->id) : currency_format($expenses))"
                icon="coins" />
        </div>
    @endif
</div>
<!-- BUDGET VS SPENT END -->
