<div class="tw-grid tw-grid-cols-2 tw-gap-3 sm:tw-grid-cols-4">
    <div>
        <p class="tw-mb-0.5 tw-text-sm text-muted">
            <?= _l('project_overview_expenses'); ?>
        </p>
        <p class="tw-font-medium tw-text-sm tw-mb-0">
            <?= e(app_format_money(sum_from_table(db_prefix() . 'expenses', ['where' => ['project_id' => $project->id], 'field' => 'amount']), $currency)); ?>
        </p>
    </div>
    <div>
        <p class="tw-mb-0.5 tw-text-sm text-info">
            <?= _l('project_overview_expenses_billable'); ?>
        </p>
        <p class="tw-font-medium tw-text-sm tw-mb-0">
            <?= e(app_format_money(sum_from_table(db_prefix() . 'expenses', ['where' => ['project_id' => $project->id, 'billable' => 1], 'field' => 'amount']), $currency)); ?>
        </p>
    </div>
    <div>
        <p class="tw-mb-0.5 tw-text-sm text-success">
            <?= _l('project_overview_expenses_billed'); ?>
        </p>
        <p class="tw-font-medium tw-text-sm tw-mb-0">
            <?= e(app_format_money(sum_from_table(db_prefix() . 'expenses', ['where' => ['project_id' => $project->id, 'invoiceid !=' => 'NULL', 'billable' => 1], 'field' => 'amount']), $currency)); ?>
        </p>
    </div>
    <div>
        <p class="tw-mb-0.5 tw-text-sm text-danger">
            <?= _l('project_overview_expenses_unbilled'); ?>
        </p>
        <p class="tw-font-medium tw-text-sm tw-mb-0">
            <?= e(app_format_money(sum_from_table(db_prefix() . 'expenses', ['where' => ['project_id' => $project->id, 'invoiceid IS NULL', 'billable' => 1], 'field' => 'amount']), $currency)); ?>
        </p>
    </div>
</div>