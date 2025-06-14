<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <h4 class="tw-mt-0 tw-font-bold tw-text-xl tw-mb-3">
                    <?= _l('openai_fine_tuning'); ?>
                </h4>
                <hr class="hr-panel-heading" />

                <div class="sm:tw-grid sm:tw-grid-cols-12 sm:tw-gap-x-4 tw-auto-rows-[1fr]">
                    <div class="tw-col-span-6">
                        <div class="panel tw-min-h-[335px]">
                            <div class="panel-heading tw-font-medium">
                                <?= _l('fine_tuning_source_data'); ?>
                            </div>
                            <div class="panel-body">
                                <p>
                                    <strong><?= _l('available_articles'); ?>:</strong>
                                    <?= $article_count; ?>
                                </p>
                                <p>
                                    <strong><?= _l('predefined_replies'); ?>:</strong>
                                    <?= $predefined_replies_count; ?>
                                </p>

                                <?php hooks()->do_action('openai_after_fine_tuning_html_data'); ?>

                                <?php if (! $meets_fine_tuning_requirements): ?>
                                <div class="alert alert-warning">
                                    <?= _l('fine_tuning_min_requirements'); ?>
                                </div>
                                <?php endif; ?>

                                <hr />

                                <div class="form-group select-placeholder">
                                    <label for="fine_tuning_model_select" class="control-label">
                                        <?= _l('fine_tuning_base_model'); ?>
                                    </label>
                                    <select id="fine_tuning_model_select" class="form-control selectpicker"
                                        name="fine_tuning_base_model">
                                        <?php foreach ($fine_tuning_models as $model): ?>
                                        <option
                                            value="<?= $model['id']; ?>"
                                            <?= $fine_tuning_base_model == $model['id'] ? 'selected' : ''; ?>>
                                            <?= $model['name']; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="text-muted mtop5">
                                        <?= _l('fine_tuning_base_model_description'); ?>
                                    </p>
                                </div>

                                <hr />
                                <div>
                                    <button id="start-finetuning" class="btn btn-primary"
                                        <?= (! $can_fine_tune) ? 'disabled' : ''; ?>>
                                        <?= ! $our_fine_tuned_model ? _l('start_fine_tuning') : _l('retrain_model'); ?>
                                    </button>
                                </div>
                                <?php if ($our_fine_tuned_model): ?>
                                <p class="tw-mt-2 tw-mb-0">
                                    <?= _l('retrain_model_description'); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="tw-col-span-6">
                        <div class="panel tw-min-h-[335px]">
                            <div class="panel-heading tw-font-medium">
                                <?= _l('last_fine_tuning_job'); ?>
                            </div>
                            <div class="panel-body">
                                <?php if (! empty($last_job_id)): ?>
                                <p>
                                    <strong><?= _l('job_id'); ?>:</strong>
                                    <span
                                        class="text-muted"><?= $last_job_id; ?></span>
                                </p>

                                <p>
                                    <strong><?= _l('fine_tuning_base_model'); ?>:</strong>
                                    <span
                                        class="text-muted"><?= explode(':', $last_job['model'] ?? '')[1] ?? $fine_tuning_base_model; ?></span>
                                </p>

                                <div id="job-status-container">
                                    <?php if (! empty($last_job) && isset($last_job)): ?>
                                    <p>
                                        <strong><?= _l('status'); ?>:</strong>
                                        <span
                                            class="tw-inline-flex tw-gap-x-2 label <?= $last_job['status'] === 'succeeded' ? 'label-success' : 'label-info'; ?>">
                                            <?php if ($last_job['status'] !== 'succeeded' && $last_job['status'] !== 'cancelled' && $last_job['status'] !== 'failed'): ?>
                                            <i class="fa fa-spinner fa-spin"></i>
                                            <?php endif; ?>
                                            <?= $last_job['status']; ?>
                                        </span>
                                    </p>

                                    <?php if (isset($last_job['model']) && ! empty($last_job['model'])): ?>
                                    <p>
                                        <strong><?= _l('fine_tuned_model'); ?>:</strong>
                                        <?= $last_job['model']; ?>
                                    </p>
                                    <?php endif; ?>

                                    <?php if (isset($last_job['created_at'])): ?>
                                    <p>
                                        <strong><?= _l('created_at'); ?>:</strong>
                                        <?= _dt(date('Y-m-d H:i:s', $last_job['created_at'])); ?>
                                    </p>
                                    <?php endif; ?>

                                    <?php if (isset($last_job['finished_at']) && ! empty($last_job['finished_at'])): ?>
                                    <p>
                                        <strong><?= _l('finished_at'); ?>:</strong>
                                        <?= _dt(date('Y-m-d H:i:s', $last_job['finished_at'])); ?>
                                    </p>
                                    <?php endif; ?>
                                    <?php else: ?>
                                    <div class="alert alert-info">
                                        <?= _l('loading_job_status'); ?>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (isset($last_job['error']) && ! empty($last_job['error'])): ?>
                                    <p class="text-danger">
                                        <strong><?= _l('error'); ?>:</strong>
                                        <?= $last_job['error']; ?>
                                    </p>
                                    <?php endif; ?>
                                </div>

                                <?php if ($last_job['status'] !== 'succeeded' && $last_job['status'] !== 'cancelled' && $last_job['status'] !== 'failed'): ?>
                                <hr />
                                <button id="check-status" class="btn btn-default">
                                    <?= _l('refresh_status'); ?>
                                </button>
                                <?php endif; ?>
                                <?php else: ?>
                                ...
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="row mtop20">
                    <div class="col-md-12">
                        <h4 class="tw-text-base tw-font-semibold tw-mb-4">
                            <?= _l('fine_tuned_models'); ?>
                        </h4>

                        <div class="panel">
                            <div class="panel-body">
                                <?php if (empty($fine_tuned_models)): ?>
                                <div class="alert alert-warning tw-mb-0">
                                    <?= _l('no_fine_tuned_models'); ?>
                                </div>
                                <?php else: ?>
                                <div class="checkbox checkbox-primary no-margin">
                                    <input type="checkbox" id="use-fine-tuning"
                                        <?= $use_fine_tuning ? 'checked' : ''; ?>>
                                    <label
                                        for="use-fine-tuning"><?= _l('enable_fine_tuning'); ?></label>
                                </div>

                                <div class="table-responsive -tw-mx-6">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th class="!tw-pl-6">
                                                    <?= _l('model_id'); ?>
                                                </th>
                                                <th><?= _l('created_at'); ?>
                                                </th>
                                                <th><?= _l('owned_by'); ?>
                                                </th>
                                                <th><?= _l('options'); ?>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($fine_tuned_models as $model): ?>
                                            <tr>
                                                <td class="!tw-pl-6">
                                                    <?= $model['id']; ?>
                                                    <?php if ($current_fine_tuned_model === $model['id']): ?>
                                                    <span
                                                        class="label label-success"><?= _l('active'); ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($model['is_our'] && strpos($model['id'], ':ckpt-step-') === false): ?>
                                                    <span
                                                        class="label label-default"><?= _l('model_is_recommended'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= _dt(date('Y-m-d H:i:s', $model['created_at'])); ?>
                                                </td>
                                                <td><?= $model['owned_by']; ?>
                                                </td>
                                                <td>
                                                    <?php if ($current_fine_tuned_model !== $model['id']): ?>
                                                    <button class="btn btn-xs btn-primary set-model"
                                                        data-model-id="<?= $model['id']; ?>">
                                                        <?= _l('set_as_active'); ?>
                                                    </button>
                                                    <?php endif; ?>
                                                    <?php if ($model['is_our']): ?>
                                                    <button class="btn btn-xs btn-danger delete-model"
                                                        data-model-id="<?= $model['id']; ?>">
                                                        <?= _l('delete'); ?>
                                                    </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

<script>
    $(function() {
        // Save fine-tuning base model
        $('#fine_tuning_model_select').on('change', function() {
            var selectedModel = $(this).val();

            $.ajax({
                url: admin_url + 'openai/finetuning/set_base_model',
                type: 'POST',
                data: {
                    model: selectedModel
                },
                dataType: 'json'
            });
        });

        // Start fine-tuning
        $('#start-finetuning').on('click', function() {
            var btn = $(this);
            btn.prop('disabled', true).html(
                '<i class="fa fa-spinner fa-spin"></i> <?= _l('processing'); ?>'
            );

            $.ajax({
                url: admin_url + 'openai/finetuning/start_job',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert_float('success', response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        alert_float('danger', response.message);
                        btn.prop('disabled', false).html(
                            '<?= _l('start_fine_tuning'); ?>'
                        );
                    }
                },
                error: function() {
                    alert_float('danger',
                        '<?= _l('error_processing_request'); ?>'
                    );
                    btn.prop('disabled', false).html(
                        '<?= _l('start_fine_tuning'); ?>'
                    );
                }
            });
        });

        // Check status
        $('#check-status').on('click', function() {
            var btn = $(this);
            btn.prop('disabled', true).html(
                '<i class="fa fa-spinner fa-spin"></i> <?= _l('processing'); ?>'
            );

            $.ajax({
                url: admin_url + 'openai/finetuning/check_status',
                type: 'POST',
                data: {
                    job_id: '<?= $last_job_id; ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    } else {
                        alert_float('danger', response.message);
                    }

                    btn.prop('disabled', false).html(
                        '<?= _l('refresh_status'); ?>'
                    );
                },
                error: function() {
                    alert_float('danger',
                        '<?= _l('error_processing_request'); ?>'
                    );
                    btn.prop('disabled', false).html(
                        '<?= _l('refresh_status'); ?>'
                    );
                }
            });
        });

        // Toggle fine-tuning
        $('#use-fine-tuning').on('change', function() {
            var useFineTuning = $(this).is(':checked');

            $.ajax({
                url: admin_url + 'openai/finetuning/toggle_use',
                type: 'POST',
                data: {
                    use_fine_tuning: useFineTuning
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert_float('success', response.message);
                    } else {
                        alert_float('danger', response.message);
                    }
                }
            });
        });

        // Set active model
        $('.set-model').on('click', function() {
            var btn = $(this);
            var modelId = btn.data('model-id');

            $.ajax({
                url: admin_url + 'openai/finetuning/set_model',
                type: 'POST',
                data: {
                    model_id: modelId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert_float('success', response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert_float('danger', response.message);
                    }
                }
            });
        });

        // Delete model
        $('.delete-model').on('click', function() {
            var btn = $(this);
            var modelId = btn.data('model-id');

            if (confirm(
                    '<?= _l('confirm_delete_fine_tuned_model'); ?>'
                )) {
                $.ajax({
                    url: admin_url + 'openai/finetuning/delete_model',
                    type: 'POST',
                    data: {
                        model_id: modelId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert_float('success', response.message);
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            alert_float('danger', response.message);
                        }
                    }
                });
            }
        });
    });
</script>