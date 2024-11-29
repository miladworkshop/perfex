<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div id="vueApp">
    <div class="sm:tw-flex tw-mb-3 md:tw-mb-6">
        <div class="tw-flex-1"></div>
        <div id="invoices_total" data-type="badge" class="tw-mt-2 md:tw-mt-0 empty:tw-min-h-[60px]"></div>
    </div>
    <?php $this->load->view('admin/invoices/quick_stats'); ?>
    <div class="panel_s">
        <div class="panel-body">
            <div class="project_invoices">
                <?php include_once APPPATH . 'views/admin/invoices/filter_params.php'; ?>

                <?php $this->load->view('admin/invoices/list_template', [
                    'table'    => $invoices_table,
                    'table_id' => $invoices_table->id(),
                ]); ?>
            </div>
        </div>
    </div>
</div>