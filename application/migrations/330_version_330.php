<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_330 extends CI_Migration
{
    public function up(): void
    {
        add_option('ai_provider', 'openai');
        add_option('ai_system_prompt', 'You are a support representative.');
        add_option('ai_enable_ticket_summarization', '0');
        add_option('ai_enable_ticket_reply_suggestions', '0');
        add_option('openai_max_token', 500);
        add_option('openai_our_fine_tuned_model', '');
        add_option('openai_fine_tuned_model', '');
        add_option('openai_fine_tuning_last_job_id', '');
        add_option('openai_fine_tuning_base_model', '');
        add_option('openai_use_fine_tuning', '0');

        get_instance()->load->library('app_modules');
        if (! get_instance()->app_modules->is_active('openai')) {
            get_instance()->app_modules->activate('openai');
        }
    }
}
