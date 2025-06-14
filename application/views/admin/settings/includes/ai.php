<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="com-md-12">
    <?= render_select('settings[ai_provider]', app\services\ai\AiProviderRegistry::getAllProviders(), ['id', 'name'], 'settings_ai_provider', get_option('ai_provider')); ?>
</div>

<hr />

<div class="com-md-12">
    <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 tw-mr-1" data-toggle="tooltip"
        data-title="<?= _l('settings_ai_system_prompt_help'); ?>"></i>
    <?= render_textarea('settings[ai_system_prompt]', 'settings_ai_system_prompt', get_option('ai_system_prompt')); ?>
</div>

<hr />

<div class="com-md-12">
    <?php render_yes_no_option('ai_enable_ticket_summarization', 'settings_ai_enable_ticket_summarization', 'settings_ai_enable_ticket_summarization_help'); ?>
</div>

<hr />

<div class="com-md-12">
    <?php render_yes_no_option('ai_enable_ticket_reply_suggestions', 'settings_ai_enable_ticket_reply_suggestions', 'settings_ai_enable_ticket_reply_suggestions_help'); ?>
</div>

<?php hooks()->do_action('settings_ai'); ?>