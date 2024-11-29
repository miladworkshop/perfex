<?php $this->load->view('admin/clients/vault_confirm_password'); ?>

<p class="tw-font-medium tw-text-sm tw-mb-4">
    <a href="#" onclick="slideToggle('#project_vault_entries'); return false;"
        class="tw-inline-flex tw-items-center tw-space-x-1">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="tw-w-5 tw-h-5">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
        </svg>
        <span>
            <?= _l('project_shared_vault_entry_login_details'); ?>
        </span>
    </a>
</p>

<div id="project_vault_entries" class="hide tw-mb-4 tw-bg-neutral-50 tw-px-4 tw-py-2 tw-rounded-md">
    <?php foreach ($project->shared_vault_entries as $vault_entry) { ?>
    <div class="tw-my-3">
        <div class="row"
            id="<?= 'vaultEntry-' . $vault_entry['id']; ?>">
            <div class="col-md-6">
                <p class="mtop5">
                    <b><?= _l('server_address'); ?>:
                    </b><?= e($vault_entry['server_address']); ?>
                </p>
                <p class="tw-mb-0">
                    <b><?= _l('port'); ?>:
                    </b><?= e(! empty($vault_entry['port']) ? $vault_entry['port'] : _l('no_port_provided')); ?>
                </p>
                <p class="tw-mb-0">
                    <b><?= _l('vault_username'); ?>:
                    </b><?= e($vault_entry['username']); ?>
                </p>
                <p class="no-margin">
                    <b><?= _l('vault_password'); ?>:
                    </b><span class="vault-password-fake">
                        <?= str_repeat('&bull;', 10); ?>
                    </span><span class="vault-password-encrypted"></span> <a href="#"
                        class="vault-view-password mleft10" data-toggle="tooltip"
                        data-title="<?= _l('view_password'); ?>"
                        onclick="vault_re_enter_password(<?= e($vault_entry['id']); ?>,this); return false;"><i
                            class="fa fa-lock" aria-hidden="true"></i></a>
                </p>
            </div>
            <div class="col-md-6">
                <?php if (! empty($vault_entry['description'])) { ?>
                <p class="tw-mb-0">
                    <b><?= _l('vault_description'); ?>:
                    </b><br /><?= process_text_content_for_display($vault_entry['description']); ?>
                </p>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php } ?>
</div>