<div class="project-overview-timesheets-chart">
    <div class="tw-flex tw-items-center tw-justify-between tw-mb-1.5">
        <h4 class="tw-text-sm tw-text-neutral-600 tw-mb-3">
            <i class="fa-regular fa-hourglass tw-mr-1.5"></i>
            <?= _l('project_overview_total_logged_hours'); ?>
        </h4>

        <div class="dropdown">
            <a href="#"
                class="dropdown-toggle tw-text-sm tw-text-neutral-500 hover:tw-text-neutral-800 active:tw-text-neutral-800 focus:tw-text-neutral-800"
                id="dropdownMenuProjectLoggedTime" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                <?php if (! $this->input->get('overview_chart')) {
                    echo _l('this_week');
                } else {
                    echo _l($this->input->get('overview_chart'));
                } ?>
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuProjectLoggedTime">
                <li>
                    <a
                        href="<?= admin_url('projects/view/' . $project->id . '?group=project_overview&overview_chart=this_week'); ?>">
                        <?= _l('this_week'); ?>
                    </a>
                </li>
                <li>
                    <a
                        href="<?= admin_url('projects/view/' . $project->id . '?group=project_overview&overview_chart=last_week'); ?>">
                        <?= _l('last_week'); ?>
                    </a>
                </li>
                <li>
                    <a
                        href="<?= admin_url('projects/view/' . $project->id . '?group=project_overview&overview_chart=this_month'); ?>">
                        <?= _l('this_month'); ?>
                    </a>
                </li>
                <li>
                    <a
                        href="<?= admin_url('projects/view/' . $project->id . '?group=project_overview&overview_chart=last_month'); ?>">
                        <?= _l('last_month'); ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <canvas id="timesheetsChart" style="max-height:250px;" class="empty:tw-w-[250px]" width="250" height="250"></canvas>
</div>