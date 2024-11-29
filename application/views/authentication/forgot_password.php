<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('authentication/includes/head.php'); ?>

<body class="tw-bg-neutral-100 authentication forgot-password">

    <div class="tw-max-w-md tw-mx-auto tw-pt-24 authentication-form-wrappe tw-relative tw-z-20">
        <div class="company-logo text-center">
            <?= get_dark_company_logo(); ?>
        </div>

        <h1 class="tw-text-2xl tw-text-neutral-800 text-center tw-font-semibold tw-mb-5">
            <?= _l('admin_auth_forgot_password_heading'); ?>
        </h1>

        <div
            class="tw-bg-white tw-mx-2 sm:tw-mx-6 tw-py-8 tw-px-6 sm:tw-px-8 tw-shadow-sm tw-rounded-lg tw-border tw-border-solid tw-border-neutral-600/20">
            <?= form_open($this->uri->uri_string()); ?>

            <?= validation_errors('<div class="alert alert-danger text-center">', '</div>'); ?>

            <?php $this->load->view('authentication/includes/alerts'); ?>

            <?= render_input('email', 'admin_auth_forgot_password_email', set_value('email'), 'email'); ?>

            <button type="submit" class="btn btn-primary btn-block tw-font-semibold tw-py-2">
                <?= _l('admin_auth_forgot_password_button'); ?>
            </button>

            <?= form_close(); ?>
        </div>

    </div>
</body>

</html>