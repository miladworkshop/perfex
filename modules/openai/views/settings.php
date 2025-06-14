<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="com-md-12">
    <?= render_input('settings[openai_api_key]', _l('openai_api_key'), get_option('openai_api_key'), 'password'); ?>
</div>
<hr />

<div class="com-md-12">
    <?php
    $models = Perfexcrm\Openai\OpenAiProvider::getModels();
echo render_select('settings[openai_model]', $models, ['id', 'name'], 'openai_model', get_option('openai_model'), get_option('openai_use_fine_tuning') == '1' ? ['disabled' => true] : []);
?>

    <?= render_input('settings[openai_max_token]', _l('openai_max_token'), get_option('openai_max_token'), 'number'); ?>
</div>

<hr class="hr-panel-heading" />

<h4 class="bold">
    <?= _l('advanced_features'); ?>
</h4>

<div class="row">
    <div class="col-md-12">
        <a href="<?= admin_url('openai/finetuning'); ?>"
            class="btn btn-primary">
            <i class="fa fa-code-fork"></i>
            <?= _l('openai_fine_tuning'); ?>
        </a>
        <p class="mtop10">
            <?= _l('openai_fine_tuning_description'); ?>
        </p>
    </div>
</div>