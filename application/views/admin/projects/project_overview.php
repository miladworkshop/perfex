<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<p class="project-info tw-mb-0 tw-font-medium tw-text-base tw-tracking-tight">
    <?= _l('project_progress_text'); ?>
    <span
        class="tw-text-neutral-500 tw-text-sm"><?= e($percent); ?>%</span>
</p>
<div class="progress progress-bar-mini">
    <div class="progress-bar progress-bar-success no-percent-text not-dynamic" role="progressbar"
        aria-valuenow="<?= e($percent); ?>" aria-valuemin="0"
        aria-valuemax="100" style="width: 0%"
        data-percent="<?= e($percent); ?>">
    </div>
</div>
<?php hooks()->do_action('admin_area_after_project_progress') ?>

<div class="panel_s tw-relative">
    <div
        class="tw-absolute tw-bg-neutral-300 tw-h-full tw-w-px tw-top-1/2 tw-left-1/2 tw-transform -tw-translate-x-1/2 -tw-translate-y-1/2 tw-z-10 tw-hidden sm:tw-block">
    </div>
    <div class="panel-body">
        <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 tw-gap-y-6 tw-gap-x-12">
            <div class="project-overview-left">

                <?php if (count($project->shared_vault_entries) > 0) { ?>
                <?php $this->load->view('admin/projects/_project_vault_entries'); ?>
                <?php } ?>

                <h4 class="tw-font-semibold tw-text-neutral-700 tw-text-base tw-mt-0 tw-mb-4">
                    <?= _l('project_overview'); ?>
                </h4>

                <?php $this->load->view('admin/projects/_project_overview_description_list'); ?>

                <?php hooks()->do_action('admin_project_overview_end_of_project_overview_left', $project) ?>
            </div>
            <div class="project-overview-right tw-space-y-8">
                <div>
                    <h4 class="tw-font-semibold tw-text-neutral-700 tw-text-base tw-mt-0 tw-mb-4">
                        <?= e($project->name); ?>
                    </h4>
                    <div class="tw-grid tw-grid-cols-2 tw-gap-4 project-progress-bars">
                        <div
                            class="project-overview-open-tasks tw-border tw-border-solid tw-border-neutral-300/80 tw-rounded-lg tw-py-2.5 tw-px-3<?= ! $project->deadline ? ' tw-col-span-2' : ''; ?>">
                            <p class="tw-text-neutral-700 tw-font-semibold tw-mb-1 tw-text-sm">
                                <span
                                    dir="ltr"><?= e($tasks_not_completed); ?>
                                    /
                                    <?= e($total_tasks); ?></span>
                                <?= _l('project_open_tasks'); ?>
                            </p>
                            <p class="tw-text-neutral-400 tw-font-normal tw-mb-0 tw-text-sm">
                                <?= e($tasks_not_completed_progress); ?>%
                            </p>
                            <div class="tw-mt-1">
                                <div class="progress no-margin progress-bar-mini">
                                    <div class="progress-bar progress-bar-success no-percent-text not-dynamic"
                                        role="progressbar"
                                        aria-valuenow="<?= e($tasks_not_completed_progress); ?>"
                                        aria-valuemin="0" aria-valuemax="100" style="width: 0%"
                                        data-percent="<?= e($tasks_not_completed_progress); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if ($project->deadline) { ?>
                        <div
                            class="project-progress-bars project-overview-days-left tw-border tw-border-solid tw-border-neutral-300/80 tw-rounded-lg tw-py-2.5 tw-px-3">
                            <p class="tw-text-neutral-700 tw-font-semibold tw-mb-1 tw-text-sm">
                                <span
                                    dir="ltr"><?= e($project_days_left); ?>
                                    /
                                    <?= e($project_total_days); ?></span>
                                <?= _l('project_days_left'); ?>
                            </p>
                            <p class="tw-text-neutral-400 tw-font-normal tw-mb-0 tw-text-sm">
                                <?= e($project_time_left_percent); ?>%
                            </p>

                            <div class="tw-mt-1">
                                <div class="progress no-margin progress-bar-mini">
                                    <div class="progress-bar no-percent-text not-dynamic <?= ($project_time_left_percent == 0) ? 'progress-bar-warning' : 'progress-bar-success'; ?>"
                                        role="progressbar"
                                        aria-valuenow="<?= e($project_time_left_percent); ?>"
                                        aria-valuemin="0" aria-valuemax="100" style="width: 0%"
                                        data-percent="<?= e($project_time_left_percent); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>

                <?php if (staff_can('create', 'projects')) { ?>
                <?php if ($project->billing_type == 3 || $project->billing_type == 2) { ?>
                <div class="project-overview-logged-hours-finance">
                    <h4 class="tw-text-sm tw-text-neutral-600 tw-mb-3">
                        <i class="fa-solid fa-clock-rotate-left tw-mr-1.5"></i>
                        <?= _l('project_overview_total_logged_hours'); ?>
                    </h4>
                    <div class="tw-border tw-border-solid tw-border-neutral-300/80 tw-rounded-lg tw-py-2.5 tw-px-3">
                        <div class="tw-grid tw-grid-cols-2 sm:tw-grid-cols-4">
                            <div>
                                <?php $data = $this->projects_model->total_logged_time_by_billing_type($project->id); ?>
                                <p class="tw-mb-0.5 tw-text-sm text-muted">
                                    <?= _l('project_overview_logged_hours'); ?>
                                    <span
                                        class="bold"><?= e($data['logged_time']); ?></span>
                                </p>
                                <p class="tw-font-medium tw-text-sm tw-mb-0">
                                    <?= e(app_format_money($data['total_money'], $currency)); ?>
                                </p>
                            </div>
                            <div>
                                <?php $data = $this->projects_model->data_billable_time($project->id); ?>
                                <p class="tw-mb-0.5 tw-text-sm text-info">
                                    <?= _l('project_overview_billable_hours'); ?>
                                    <span
                                        class="bold"><?= e($data['logged_time']); ?></span>
                                </p>
                                <p class="tw-font-medium tw-text-sm tw-mb-0">
                                    <?= e(app_format_money($data['total_money'], $currency)); ?>
                                </p>
                            </div>
                            <div>
                                <?php $data = $this->projects_model->data_billed_time($project->id); ?>
                                <p class="tw-mb-0.5 tw-text-sm text-success">
                                    <?= _l('project_overview_billed_hours'); ?>
                                    <span
                                        class="bold"><?= e($data['logged_time']); ?></span>
                                </p>
                                <p class="tw-font-medium tw-text-sm tw-mb-0">
                                    <?= e(app_format_money($data['total_money'], $currency)); ?>
                                </p>
                            </div>
                            <div>
                                <?php $data = $this->projects_model->data_unbilled_time($project->id); ?>
                                <p class="tw-mb-0.5 tw-text-sm text-danger">
                                    <?= _l('project_overview_unbilled_hours'); ?>
                                    <span
                                        class="bold"><?= e($data['logged_time']); ?></span>
                                </p>
                                <p class="tw-font-medium tw-text-sm tw-mb-0">
                                    <?= e(app_format_money($data['total_money'], $currency)); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <div class="project-overview-expenses-finance">
                    <h4 class="tw-text-sm tw-text-neutral-600 tw-mb-3">
                        <i class="fa-regular fa-file tw-mr-1.5"></i>
                        <?= _l('project_expenses'); ?>
                    </h4>
                    <div class="tw-border tw-border-solid tw-border-neutral-300/80 tw-rounded-lg tw-py-2.5 tw-px-3">
                        <?php $this->load->view('admin/projects/_project_expenses_overview'); ?>
                    </div>
                </div>
                <?php } ?>

                <?php $this->load->view('admin/projects/_project_timesheets_chart'); ?>

                <?php hooks()->do_action('admin_project_overview_end_of_project_overview_right', $project) ?>
            </div>
        </div>
    </div>
</div>
<?php if (isset($project_overview_chart)) { ?>
<script>
    var
        project_overview_chart = <?= json_encode($project_overview_chart); ?> ;
</script>
<?php } ?>