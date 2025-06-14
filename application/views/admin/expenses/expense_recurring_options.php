<div class="form-group select-placeholder" <?php if (isset($expense) && ! empty($expense->recurring_from)) { ?>
    data-toggle="tooltip"
    data-title="<?= _l('create_recurring_from_child_error_message', [_l('expense_lowercase'), _l('expense_lowercase'), _l('expense_lowercase')]); ?>"
    <?php } ?>>
    <label for="repeat_every"
        class="control-label"><?= _l('expense_repeat_every'); ?></label>
    <select name="repeat_every" id="repeat_every" class="selectpicker" data-width="100%"
        data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>"
        <?php if (isset($expense) && ! empty($expense->recurring_from)) { ?>
        disabled <?php } ?>>
        <option value=""></option>
        <option value="1-week" <?php if (isset($expense) && $expense->repeat_every == 1 && $expense->recurring_type == 'week') {
            echo 'selected';
        } ?>><?= _l('week'); ?>
        </option>
        <option value="2-week" <?php if (isset($expense) && $expense->repeat_every == 2 && $expense->recurring_type == 'week') {
            echo 'selected';
        } ?>>2
            <?= _l('weeks'); ?>
        </option>
        <option value="1-month" <?php if (isset($expense) && $expense->repeat_every == 1 && $expense->recurring_type == 'month') {
            echo 'selected';
        } ?>>1
            <?= _l('month'); ?>
        </option>
        <option value="2-month" <?php if (isset($expense) && $expense->repeat_every == 2 && $expense->recurring_type == 'month') {
            echo 'selected';
        } ?>>2
            <?= _l('months'); ?>
        </option>
        <option value="3-month" <?php if (isset($expense) && $expense->repeat_every == 3 && $expense->recurring_type == 'month') {
            echo 'selected';
        } ?>>3
            <?= _l('months'); ?>
        </option>
        <option value="6-month" <?php if (isset($expense) && $expense->repeat_every == 6 && $expense->recurring_type == 'month') {
            echo 'selected';
        } ?>>6
            <?= _l('months'); ?>
        </option>
        <option value="1-year" <?php if (isset($expense) && $expense->repeat_every == 1 && $expense->recurring_type == 'year') {
            echo 'selected';
        } ?>>1
            <?= _l('year'); ?>
        </option>
        <option value="custom" <?php if (isset($expense) && $expense->custom_recurring == 1) {
            echo 'selected';
        } ?>><?= _l('recurring_custom'); ?>
        </option>
    </select>
</div>
<div class="recurring_custom <?php if ((isset($expense) && $expense->custom_recurring != 1) || (! isset($expense))) {
    echo 'hide';
} ?>">
    <div class="row">
        <div class="col-md-6">
            <?php $value = (isset($expense) && $expense->custom_recurring == 1 ? $expense->repeat_every : 1); ?>
            <?= render_input('repeat_every_custom', '', $value, 'number', ['min' => 1]); ?>
        </div>
        <div class="col-md-6">
            <select name="repeat_type_custom" id="repeat_type_custom" class="selectpicker" data-width="100%"
                data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                <option value="day" <?php if (isset($expense) && $expense->custom_recurring == 1 && $expense->recurring_type == 'day') {
                    echo 'selected';
                } ?>><?= _l('expense_recurring_days'); ?>
                </option>
                <option value="week" <?php if (isset($expense) && $expense->custom_recurring == 1 && $expense->recurring_type == 'week') {
                    echo 'selected';
                } ?>><?= _l('expense_recurring_weeks'); ?>
                </option>
                <option value="month" <?php if (isset($expense) && $expense->custom_recurring == 1 && $expense->recurring_type == 'month') {
                    echo 'selected';
                } ?>><?= _l('expense_recurring_months'); ?>
                </option>
                <option value="year" <?php if (isset($expense) && $expense->custom_recurring == 1 && $expense->recurring_type == 'year') {
                    echo 'selected';
                } ?>><?= _l('expense_recurring_years'); ?>
                </option>
            </select>
        </div>
    </div>
</div>
<div id="cycles_wrapper" class="<?php if (! isset($expense) || (isset($expense) && $expense->recurring == 0)) {
    echo ' hide';
}?>">
    <?php $value = (isset($expense) ? $expense->cycles : 0); ?>
    <div class="form-group recurring-cycles">
        <label
            for="cycles"><?= _l('recurring_total_cycles'); ?>
            <?php if (isset($expense) && $expense->total_cycles > 0) {
                echo '<small>' . e(_l('cycles_passed', $expense->total_cycles)) . '</small>';
            } ?>
        </label>
        <div class="input-group">
            <input type="number" class="form-control" <?php if ($value == 0) {
                echo ' disabled';
            } ?> name="cycles" id="cycles"
            value="<?= e($value); ?>"
            <?php if (isset($expense) && $expense->total_cycles > 0) {
                echo 'min="' . e($expense->total_cycles) . '"';
            } ?>>
            <div class="input-group-addon">
                <div class="checkbox">
                    <input type="checkbox" <?php if ($value == 0) {
                        echo ' checked';
                    } ?> id="unlimited_cycles">
                    <label
                        for="unlimited_cycles"><?= _l('cycles_infinity'); ?></label>
                </div>
            </div>
        </div>
    </div>
</div>
<div>
    <?php $hide_invoice_recurring_options = (isset($expense) && $expense->billable == 1) ? '' : 'hide'; ?>
    <div
        class="checkbox checkbox-primary billable_recurring_options <?= e($hide_invoice_recurring_options); ?>">
        <input type="checkbox" id="create_invoice_billable" name="create_invoice_billable" <?php if (isset($expense)) {
            if ($expense->create_invoice_billable == 1) {
                echo 'checked';
            }
        } ?>>
        <label for="create_invoice_billable"><i class="fa-regular fa-circle-question" data-toggle="tooltip"
                title="<?= _l('expense_recurring_autocreate_invoice_tooltip'); ?>"></i>
            <?= _l('expense_recurring_auto_create_invoice'); ?></label>
    </div>
</div>
<div
    class="checkbox checkbox-primary billable_recurring_options <?= e($hide_invoice_recurring_options); ?>">
    <input type="checkbox" name="send_invoice_to_customer" id="send_invoice_to_customer"
        <?= isset($expense) && $expense->send_invoice_to_customer == 1 ? 'checked' : ''; ?>>
    <label
        for="send_invoice_to_customer"><?= _l('expense_recurring_send_custom_on_renew'); ?></label>
</div>