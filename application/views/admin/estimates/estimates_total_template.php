<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (count($estimates_years) > 1 || isset($currencies)) { ?>
<div
    class="tw-inline-flex tw-w-full tw-mb-2 tw-gap-8 tw-pr-2.5 tw-justify-items-end tw-items-end [&_.caret]:!tw-top-[9px] [&_.btn]:tw-py-0 [&_.btn]:tw-mr-0 [&_.btn]:tw-h-[24px] [&_.btn]:tw-font-medium [&_select]:tw-left-auto rtl:[&_.filter-option]:!tw-p-[inherit] rtl:[&_.filter-option]:!tw-text-left [&_.dropdown-menu]:tw-mt-1">
    <?php if (isset($currencies)) { ?>
    <div class="simple-bootstrap-select">
        <select class="selectpicker tw-w-full tw-min-w-[79px]" data-width="fit" name="total_currency"
            onchange="init_estimates_total();">
            <?php foreach ($currencies as $currency) {
                $selected = '';
                if (! $this->input->post('currency')) {
                    if ($currency['isdefault'] == 1 || isset($_currency) && $_currency == $currency['id']) {
                        $selected = 'selected';
                    }
                } else {
                    if ($this->input->post('currency') == $currency['id']) {
                        $selected = 'selected';
                    }
                } ?>
            <option
                value="<?= e($currency['id']); ?>"
                <?= e($selected); ?>
                data-subtext="<?= e($currency['name']); ?>"><?= e($currency['symbol']); ?>
            </option>
            <?php
            } ?>
        </select>
    </div>
    <?php } ?>

    <?php if (count($estimates_years) > 1) { ?>
    <div class="simple-bootstrap-select">
        <select
            data-none-selected-text="<?= date('Y'); ?>"
            data-width="fit" class="selectpicker tw-w-full tw-min-w-[79px]" multiple name="estimates_total_years"
            onchange="init_estimates_total();">
            <?php foreach ($estimates_years as $year) { ?>
            <option
                value="<?= e($year['year']); ?>"
                <?php if ($this->input->post('years') && in_array($year['year'], $this->input->post('years')) || ! $this->input->post('years') && date('Y') == $year['year']) {
                    echo ' selected';
                } ?>>
                <?= e($year['year']); ?>
            </option>
            <?php } ?>
        </select>
    </div>
    <?php } ?>
</div>
<?php } ?>
<dl class="tw-grid tw-grid-cols-2 md:tw-grid-cols-2 lg:tw-grid-cols-5 tw-gap-2 tw-mb-0">
    <?php
foreach ($totals as $key => $data) {
    $class = estimate_status_color_class($data['status']);
    $name  = estimate_status_by_id($data['status']); ?>
    <div
        class="tw-border tw-border-solid tw-border-neutral-300/80 tw-rounded-md tw-bg-white odd:last:tw-col-span-2 md:odd:last:tw-col-auto">
        <div class="tw-px-4 tw-py-5 sm:tw-px-4 sm:tw-py-2">
            <dt class="tw-font-medium text-<?= e($class); ?>">
                <?= e($name); ?>
            </dt>
            <dd class="tw-mt-1 tw-flex tw-items-baseline tw-justify-between md:tw-block lg:tw-flex">
                <div class="tw-flex tw-items-baseline tw-text-base tw-font-semibold tw-text-neutral-600">
                    <?= e(app_format_money($data['total'], $data['currency_name'])); ?>
                </div>
            </dd>
        </div>
    </div>
    <?php
} ?>
</dl>
<script>
    $(function() {
        init_selectpicker();
    });
</script>