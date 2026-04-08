<?php
$companies = App\Models\Company::all();
foreach($companies as $c){
    $widgets = ['project_statistics', 'project_hours_chart'];
    foreach($widgets as $w){
        App\Models\DashboardWidget::firstOrCreate(
            ['company_id' => $c->id, 'widget_name' => $w, 'dashboard_type' => 'project-overview-dashboard'],
            ['status' => 1]
        );
    }
}
echo "Seeded widgets successfully.\n";