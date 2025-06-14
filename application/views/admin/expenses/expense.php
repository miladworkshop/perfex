<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<?php $hideTaxFields   = ! isset($expense) || ($expense->tax == 0 && $expense->tax2 == 0); ?>
<?php $hidePaymentMode = ! isset($expense) || empty($expense->paymentmode); ?>
<?php $hideName        = ! isset($expense) || empty($expense->expense_name); ?>
<?php $hideNote        = ! isset($expense) || empty($expense->note); ?>
<?php $hideReference   = ! isset($expense) || empty($expense->reference_no); ?>
<?php $hideRecurring   = ! isset($expense) || $expense->recurring == 0; ?>

<div id="wrapper">
    <div class="content">
        <div class="tw-max-w-4xl tw-mx-auto">
            <h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700">
                <?= e($title); ?>
            </h4>
            <?php if (isset($expense)) {
                echo form_hidden('is_edit', 'true');
            } ?>
            <?= form_open_multipart($this->uri->uri_string(), ['id' => 'expense-form', 'class' => 'dropzone dropzone-manual']); ?>
            <div class="panel_s">
                <div class="panel-body">
                    <?php if (isset($expense) && $expense->recurring_from != null) {
                        $recurring_expense = $this->expenses_model->get($expense->recurring_from);
                        echo '<div class="alert alert-info">' . _l('expense_recurring_from', '<a href="' . admin_url('expenses/list_expenses/' . $expense->recurring_from) . '" class="alert-link" target="_blank">' . e($recurring_expense->category_name) . (! empty($recurring_expense->expense_name) ? ' (' . e($recurring_expense->expense_name) . ')' : '') . '</a></div>');
                    } ?>
                    <div
                        class="tw-bg-neutral-50 tw-overflow-hidden tw-rounded-t-md tw-p-6 tw-border-b -tw-mt-6 -tw-mx-6 tw-border-solid tw-border-neutral-200 tw-mb-4">
                        <?php if (isset($expense) && $expense->attachment !== '') { ?>
                        <div class="row">
                            <div class="col-md-10">
                                <h4 class="tw-mt-0 tw-font-bold tw-text-base">
                                    <?= _l('expense_receipt'); ?>
                                </h4>
                                <i class="fa-solid fa-paperclip tw-text-neutral-500 ltr:tw-mr-1 rtl:tw-ml-1"></i>
                                <a
                                    href="<?= site_url('download/file/expense/' . $expense->expenseid); ?>">
                                    <?= e($expense->attachment); ?>
                                </a>
                            </div>
                            <?php if ($expense->attachment_added_from == get_staff_user_id() || is_admin()) { ?>
                            <div class="col-md-2 text-right">
                                <a href="<?= admin_url('expenses/delete_expense_attachment/' . $expense->expenseid); ?>"
                                    class="_delete tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"><i
                                        class="fa-regular fa-trash-can"></i></a>
                            </div>
                            <?php } ?>
                        </div>
                        <?php } ?>
                        <?php if (! isset($expense) || (isset($expense) && $expense->attachment == '')) { ?>
                        <div id="dropzoneDragArea" class="dz-default dz-message">
                            <span><?= _l('expense_add_edit_attach_receipt'); ?></span>
                        </div>
                        <div class="dropzone-previews"></div>
                        <?php } ?>
                    </div>
                    <?php if ($hideRecurring) { ?>
                    <a href="#" class="tw-mb-4 -tw-mt-1 tw-block"
                        onclick="return onExpenseAddFieldClick(this, 'expenseRecurringOptions')">
                        +
                        <?= _l('expense_recurring_indicator'); ?>
                    </a>
                    <?php } ?>
                    <div id="expenseRecurringOptions"
                        style="<?= $hideRecurring ? 'display:none' : ''; ?>">
                        <?php $this->load->view('admin/expenses/expense_recurring_options'); ?>
                    </div>

                    <?php hooks()->do_action('before_expense_form_name', $expense ?? null); ?>

                    <?php if ($hideName) { ?>
                    <a href="#" class="tw-mb-4 -tw-mt-1 tw-block"
                        onclick="return onExpenseAddFieldClick(this, 'expenseNameField')">
                        +
                        <?= _l('expense_name'); ?>
                    </a>
                    <?php } ?>

                    <div id="expenseNameField"
                        style="<?= $hideName ? 'display:none' : ''; ?>">
                        <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 ltr:tw-mr-1 rtl:tw-ml-1"
                            data-toggle="tooltip"
                            data-title="<?= _l('expense_name_help'); ?> - <?= e(_l('expense_field_billable_help', _l('expense_name'))); ?>"></i>

                        <?php $value = (isset($expense) ? $expense->expense_name : ''); ?>
                        <?= render_input('expense_name', 'expense_name', $value); ?>
                    </div>

                    <?php if ($hideReference) { ?>
                    <a href="#" class="tw-mb-4 -tw-mt-1 tw-block"
                        onclick="return onExpenseAddFieldClick(this, 'expenseReferenceField')">
                        +
                        <?= _l('expense_add_edit_reference_no'); ?>
                    </a>
                    <?php } ?>

                    <div id="expenseReferenceField"
                        style="<?= $hideReference ? 'display:none' : ''; ?>">
                        <?php $value = (isset($expense) ? $expense->reference_no : ''); ?>
                        <?= render_input('reference_no', 'expense_add_edit_reference_no', $value); ?>
                    </div>

                    <?php if ($hideNote) { ?>
                    <a href="#" class="tw-mb-4 -tw-mt-1 tw-block"
                        onclick="return onExpenseAddFieldClick(this, 'expenseNoteField')">
                        +
                        <?= _l('expense_add_edit_note'); ?>
                    </a>
                    <?php } ?>

                    <div id="expenseNoteField"
                        style="<?= $hideNote ? 'display:none' : ''; ?>">
                        <i class="fa-regular fa-circle-question pull-left tw-mt-0.5 ltr:tw-mr-1 rtl:tw-ml-1"
                            data-toggle="tooltip"
                            data-title="<?= e(_l('expense_field_billable_help', _l('expense_add_edit_note'))); ?>"></i>
                        <?php $value = (isset($expense) ? $expense->note : ''); ?>
                        <?= render_textarea('note', 'expense_add_edit_note', $value, ['rows' => 2], []); ?>
                    </div>

                    <div class="tw-mt-6">
                        <?php $selected = (isset($expense) ? $expense->category : ''); ?>
                        <?php if (is_admin() || get_option('staff_members_create_inline_expense_categories') == '1') {
                            echo render_select_with_input_group('category', $categories, ['id', 'name'], 'expense_category', $selected, '<div class="input-group-btn"><a href="#" class="btn btn-default" onclick="new_category();return false;"><i class="fa fa-plus"></i></a></div>');
                        } else {
                            echo render_select('category', $categories, ['id', 'name'], 'expense_category', $selected);
                        } ?>
                    </div>
                    <?php
                        $value        = (isset($expense) ? _d($expense->date) : _d(date('Y-m-d'))); ?>
                    <?php $date_attrs = []; ?>
                    <?php if (isset($expense) && $expense->recurring > 0 && $expense->last_recurring_date != null) {
                        $date_attrs['disabled'] = true;
                    } ?>
                    <?= render_date_input('date', 'expense_add_edit_date', $value, $date_attrs); ?>

                    <div
                        class="tw-bg-neutral-50/30 tw-shadow-sm tw-px-5 tw-py-4 tw-rounded-lg tw-border tw-border-solid tw-border-neutral-200 tw-mt-4 tw-mb-6">
                        <div class="tw-flex tw-gap-x-1.5">
                            <div class="tw-grow">
                                <?php $value = (isset($expense) ? $expense->amount : ''); ?>
                                <?= render_input('amount', 'expense_add_edit_amount', $value, 'number'); ?>
                            </div>
                            <?php
$currency_attr = ['disabled' => true, 'data-show-subtext' => true, 'data-width' => '120px'];

$currency_attr = apply_filters_deprecated('expense_currency_disabled', [$currency_attr], '2.3.0', 'expense_currency_attributes');

foreach ($currencies as $currency) {
    if ($currency['isdefault'] == 1) {
        $currency_attr['data-base'] = $currency['id'];
    }
    if (isset($expense)) {
        if ($currency['id'] == $expense->currency) {
            $selected = $currency['id'];
        }
        if ($expense->billable == 0) {
            if ($expense->clientid) {
                $c = $this->clients_model->get_customer_default_currency($expense->clientid);
                if ($c != 0) {
                    $customer_currency = $c;
                }
            }
        }
    } else {
        if (isset($customer_id)) {
            $c = $this->clients_model->get_customer_default_currency($customer_id);
            if ($c != 0) {
                $customer_currency = $c;
            }
        }
        if ($currency['isdefault'] == 1) {
            $selected = $currency['id'];
        }
    }
}
$currency_attr = hooks()->apply_filters('expense_currency_attributes', $currency_attr);
?>
                            <div id="expense_currency" class="tw-self-end">
                                <?= render_select('currency', $currencies, ['id', 'name', 'symbol'], '', $selected, $currency_attr); ?>
                            </div>
                        </div>

                        <?php if ($hideTaxFields) { ?>
                        <a href="#" class="tw-mb-4 -tw-mt-2 tw-block"
                            onclick="return onExpenseAddFieldClick(this, 'taxFields')">
                            + <?= _l('tax'); ?>
                        </a>
                        <?php } ?>

                        <div id="taxFields"
                            style="<?= $hideTaxFields ? 'display:none' : ''; ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group select-placeholder">
                                        <label class="control-label"
                                            for="tax"><?= _l('tax_1'); ?></label>
                                        <select class="selectpicker display-block" data-width="100%" name="tax"
                                            data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                                            <option value="">
                                                <?= _l('no_tax'); ?>
                                            </option>
                                            <?php foreach ($taxes as $tax) {
                                                $selected = '';
                                                if (isset($expense)) {
                                                    if ($tax['id'] == $expense->tax) {
                                                        $selected = 'selected';
                                                    }
                                                } ?>
                                            <option
                                                value="<?= e($tax['id']); ?>"
                                                <?= e($selected); ?>
                                                data-percent="<?= e($tax['taxrate']); ?>"
                                                data-subtext="<?= e($tax['name']); ?>">
                                                <?= e($tax['taxrate']); ?>%
                                            </option>
                                            <?php
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group select-placeholder">
                                        <label class="control-label"
                                            for="tax2"><?= _l('tax_2'); ?></label>
                                        <select class="selectpicker display-block" data-width="100%" name="tax2"
                                            data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>"
                                            <?php if (! isset($expense) || isset($expense) && $expense->tax == 0) {
                                                echo 'disabled';
                                            } ?>>
                                            <option value="">
                                                <?= _l('no_tax'); ?>
                                            </option>
                                            <?php foreach ($taxes as $tax) {
                                                $selected = '';
                                                if (isset($expense)) {
                                                    if ($tax['id'] == $expense->tax2) {
                                                        $selected = 'selected';
                                                    }
                                                } ?>
                                            <option
                                                value="<?= e($tax['id']); ?>"
                                                <?= e($selected); ?>
                                                data-percent="<?= e($tax['taxrate']); ?>"
                                                data-subtext="<?= e($tax['name']); ?>">
                                                <?= e($tax['taxrate']); ?>%
                                            </option>
                                            <?php
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                                <?php if (! isset($expense)) { ?>
                                <div class="col-md-12 hide" id="tax_subtract">
                                    <div class="alert alert-info">
                                        <div class="checkbox checkbox-primary no-margin">
                                            <input type="checkbox" id="tax1_included">
                                            <label for="tax1_included">
                                                <?= _l('subtract_tax_total_from_amount', '<span id="tax_subtract_total" class="bold"></span>'); ?>
                                            </label>
                                        </div>
                                        <p class="tw-text-sm tw-mt-2 tw-ml-7">
                                            <?= _l('expense_subtract_info_text'); ?>
                                        </p>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>

                        <?php if ($hidePaymentMode) { ?>
                        <a href="#" class="-tw-mt-2 tw-block"
                            onclick="return onExpenseAddFieldClick(this, 'paymentModeField')">
                            +
                            <?= _l('payment_mode'); ?>
                        </a>
                        <?php } ?>


                        <div id="paymentModeField"
                            style="<?= $hidePaymentMode ? 'display:none' : ''; ?>">
                            <?php $selected = (isset($expense) ? $expense->paymentmode : ''); ?>
                            <?= render_select('paymentmode', $payment_modes, ['id', 'name'], 'payment_mode', $selected); ?>
                        </div>
                    </div>

                    <div class="form-group select-placeholder">
                        <label for="clientid"
                            class="control-label"><?= _l('expense_add_edit_customer'); ?></label>
                        <select id="clientid" name="clientid" data-live-search="true" data-width="100%"
                            class="ajax-search"
                            data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                            <?php $selected = (isset($expense) ? $expense->clientid : ($customer_id ?? '')); ?>
                            <?php if ($selected != '') {
                                $rel_data = get_relation_data('customer', $selected);
                                $rel_val  = get_relation_values($rel_data, 'customer');
                                echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                            } ?>
                        </select>
                    </div>
                    <?php $hide_billable_options = 'hide'; ?>

                    <?php if ((isset($expense) && ($expense->billable == 1 || $expense->clientid)) || isset($customer_id)) {
                        $hide_billable_options = '';
                    } ?>

                    <div
                        class="checkbox checkbox-primary billable <?= e($hide_billable_options); ?>">
                        <input type="checkbox" id="billable" <?php if (isset($expense) && $expense->invoiceid !== null) {
                            echo 'disabled';
                        } ?> name="billable" <?php if (isset($expense)) {
                            if ($expense->billable == 1) {
                                echo 'checked';
                            }
                        } ?>>
                        <label for="billable"
                            <?php if (isset($expense) && $expense->invoiceid !== null) {
                                echo 'data-toggle="tooltip" title="' . _l('expense_already_invoiced') . '"';
                            } ?>><?= _l('expense_add_edit_billable'); ?></label>
                    </div>
                    <!-- // Show selector only if expense is already added and there is no client linked to the expense or isset customer id -->
                    <?php $hide_project_selector = (isset($expense) && $expense->clientid) || isset($customer_id) ? '' : ' hide'; ?>
                    <div
                        class="form-group projects-wrapper<?= e($hide_project_selector); ?>">
                        <label
                            for="project_id"><?= _l('project'); ?></label>
                        <div id="project_ajax_search_wrapper">
                            <select name="project_id" id="project_id" class="projects ajax-search"
                                data-live-search="true" data-width="100%"
                                data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
                                <?php if (isset($expense) && $expense->project_id) {
                                    echo '<option value="' . $expense->project_id . '" selected>' . e(get_project_name_by_id($expense->project_id)) . '</option>';
                                } ?>
                            </select>
                        </div>
                    </div>

                    <?php $rel_id = (isset($expense) ? $expense->expenseid : false); ?>
                    <?= render_custom_fields('expenses', $rel_id); ?>

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">
                            <?= _l('submit'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <?php hooks()->do_action('before_expense_form_template_close', $expense ?? null); ?>
            <?= form_close(); ?>
        </div>

    </div>
</div>
<?php $this->load->view('admin/expenses/expense_category'); ?>
<?php init_tail(); ?>
<script>
    var customer_currency = '';
    Dropzone.options.expenseForm = false;
    var expenseDropzone;
    init_ajax_project_search_by_customer_id();
    var selectCurrency = $('select[name="currency"]');

    <?php if (isset($customer_currency)) { ?>
    var customer_currency = '<?= e($customer_currency); ?>';
    <?php } ?>

    function onExpenseAddFieldClick(el, id) {
        window[id].style.display = 'block';
        el.style.display = 'none'
        let formEl = $('#' + id).find('textarea,input,select');
        formEl.focus()
        return false;
    }

    $(function() {
        $('body').on('change', '#project_id', function() {
            var project_id = $(this).val();
            if (project_id != '') {
                if (customer_currency != 0) {
                    selectCurrency.val(customer_currency);
                    selectCurrency.selectpicker('refresh');
                } else {
                    set_base_currency();
                }
            } else {
                do_billable_checkbox();
            }
        });

        if ($('#dropzoneDragArea').length > 0) {
            expenseDropzone = new Dropzone("#expense-form", appCreateDropzoneOptions({
                autoProcessQueue: false,
                clickable: '#dropzoneDragArea',
                previewsContainer: '.dropzone-previews',
                addRemoveLinks: true,
                maxFiles: 1,
                success: function(file, response) {
                    response = JSON.parse(response);
                    if (this.getUploadingFiles().length === 0 && this.getQueuedFiles()
                        .length ===
                        0) {
                        window.location.assign(response.url);
                    }
                },
            }));
        }

        appValidateForm($('#expense-form'), {
            category: 'required',
            date: 'required',
            amount: 'required',
            currency: 'required',
            repeat_every_custom: {
                min: 1
            },
        }, expenseSubmitHandler);

        $('input[name="billable"]').on('change', function() {
            do_billable_checkbox();
        });

        $('#repeat_every').on('change', function() {
            if ($(this).selectpicker('val') != '' && $('input[name="billable"]').prop('checked') ==
                true) {
                $('.billable_recurring_options').removeClass('hide');
            } else {
                $('.billable_recurring_options').addClass('hide');
            }
        });

        // hide invoice recurring options on page load
        $('#repeat_every').trigger('change');

        $('select[name="clientid"]').on('change', function() {
            customer_init();
            do_billable_checkbox();
            $('input[name="billable"]').trigger('change');
        });

        <?php if (! isset($expense)) { ?>
        $('select[name="tax"], select[name="tax2"]').on('change', function() {

            delay(function() {
                var $amount = $('#amount'),
                    taxDropdown1 = $('select[name="tax"]'),
                    taxDropdown2 = $('select[name="tax2"]'),
                    taxPercent1 = parseFloat(taxDropdown1.find('option[value="' + taxDropdown1
                        .val() + '"]').attr('data-percent')),
                    taxPercent2 = parseFloat(taxDropdown2.find('option[value="' + taxDropdown2
                        .val() + '"]').attr('data-percent')),
                    total = $amount.val();

                if (total == 0 || total == '') {
                    return;
                }

                if ($amount.attr('data-original-amount')) {
                    total = $amount.attr('data-original-amount');
                }

                total = parseFloat(total);

                if (taxDropdown1.val() || taxDropdown2.val()) {

                    $('#tax_subtract').removeClass('hide');

                    var totalTaxPercentExclude = taxPercent1;
                    if (taxDropdown2.val()) {
                        totalTaxPercentExclude += taxPercent2;
                    }

                    var totalExclude = accounting.toFixed(total - exclude_tax_from_amount(
                        totalTaxPercentExclude, total), app.options.decimal_places);
                    $('#tax_subtract_total').html(accounting.toFixed(totalExclude, app.options
                        .decimal_places));
                } else {
                    $('#tax_subtract').addClass('hide');
                }
                if ($('#tax1_included').prop('checked') == true) {
                    subtract_tax_amount_from_expense_total();
                }
            }, 200);
        });

        $('#amount').on('blur', function() {
            $(this).removeAttr('data-original-amount');
            if ($(this).val() == '' || $(this).val() == '') {
                $('#tax1_included').prop('checked', false);
                $('#tax_subtract').addClass('hide');
            } else {
                var tax1 = $('select[name="tax"]').val();
                var tax2 = $('select[name="tax2"]').val();
                if (tax1 || tax2) {
                    setTimeout(function() {
                        $('select[name="tax2"]').trigger('change');
                    }, 100);
                }
            }
        })

        $('#tax1_included').on('change', function() {

            var $amount = $('#amount'),
                total = parseFloat($amount.val());

            // da pokazuva total za 2 taxes  Subtract TAX total (136.36) from expense amount
            if (total == 0) {
                return;
            }

            if ($(this).prop('checked') == false) {
                $amount.val($amount.attr('data-original-amount'));
                return;
            }

            subtract_tax_amount_from_expense_total();
        });
        <?php } ?>
    });

    function subtract_tax_amount_from_expense_total() {
        var $amount = $('#amount'),
            total = parseFloat($amount.val()),
            taxDropdown1 = $('select[name="tax"]'),
            taxDropdown2 = $('select[name="tax2"]'),
            taxRate1 = parseFloat(taxDropdown1.find('option[value="' + taxDropdown1.val() + '"]').attr('data-percent')),
            taxRate2 = parseFloat(taxDropdown2.find('option[value="' + taxDropdown2.val() + '"]').attr('data-percent'));

        var totalTaxPercentExclude = taxRate1;
        if (taxRate2) {
            totalTaxPercentExclude += taxRate2;
        }

        if ($amount.attr('data-original-amount')) {
            total = parseFloat($amount.attr('data-original-amount'));
        }

        $amount.val(exclude_tax_from_amount(totalTaxPercentExclude, total));

        if ($amount.attr('data-original-amount') == undefined) {
            $amount.attr('data-original-amount', total);
        }
    }

    function customer_init() {
        var customer_id = $('select[name="clientid"]').val();
        var projectAjax = $('select[name="project_id"]');
        var clonedProjectsAjaxSearchSelect = projectAjax.html('').clone();
        var projectsWrapper = $('.projects-wrapper');
        projectAjax.selectpicker('destroy').remove();
        projectAjax = clonedProjectsAjaxSearchSelect;
        $('#project_ajax_search_wrapper').append(clonedProjectsAjaxSearchSelect);
        init_ajax_project_search_by_customer_id();
        if (!customer_id) {
            set_base_currency();
            projectsWrapper.addClass('hide');
        }
        $.get(admin_url + 'expenses/get_customer_change_data/' + customer_id, function(response) {
            if (customer_id && response.customer_has_projects) {
                projectsWrapper.removeClass('hide');
            } else {
                projectsWrapper.addClass('hide');
            }
            var client_currency = parseInt(response.client_currency);
            if (client_currency != 0) {
                customer_currency = client_currency;
                do_billable_checkbox();
            } else {
                customer_currency = '';
                set_base_currency();
            }
        }, 'json');
    }

    function expenseSubmitHandler(form) {

        selectCurrency.prop('disabled', false);

        $('select[name="tax2"]').prop('disabled', false);
        $('input[name="billable"]').prop('disabled', false);
        $('input[name="date"]').prop('disabled', false);

        $.post(form.action, $(form).serialize()).done(function(response) {
            response = JSON.parse(response);
            if (response.expenseid) {
                if (typeof(expenseDropzone) !== 'undefined') {
                    if (expenseDropzone.getQueuedFiles().length > 0) {
                        expenseDropzone.options.url = admin_url + 'expenses/add_expense_attachment/' + response
                            .expenseid;
                        expenseDropzone.processQueue();
                    } else {
                        window.location.assign(response.url);
                    }
                } else {
                    window.location.assign(response.url);
                }
            } else {
                window.location.assign(response.url);
            }
        });
        return false;
    }

    function do_billable_checkbox() {
        var val = $('select[name="clientid"]').val();
        if (val != '') {
            $('.billable').removeClass('hide');
            if ($('input[name="billable"]').prop('checked') == true) {
                if ($('#repeat_every').selectpicker('val') != '') {
                    $('.billable_recurring_options').removeClass('hide');
                } else {
                    $('.billable_recurring_options').addClass('hide');
                }
                if (customer_currency != '') {
                    selectCurrency.val(customer_currency);
                    selectCurrency.selectpicker('refresh');
                } else {
                    set_base_currency();
                }
            } else {
                $('.billable_recurring_options').addClass('hide');
                // When project is selected, the project currency will be used, either customer currency or base currency
                if ($('#project_id').selectpicker('val') == '') {
                    set_base_currency();
                }
            }
        } else {
            set_base_currency();
            $('.billable').addClass('hide');
            $('.billable_recurring_options').addClass('hide');
        }
    }

    function set_base_currency() {
        selectCurrency.val(selectCurrency.data('base'));
        selectCurrency.selectpicker('refresh');
    }
</script>
</body>

</html>