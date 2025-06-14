<?php

use app\services\ai\AiProviderRegistry;
use Perfexcrm\Openai\OpenAiProvider;

defined('BASEPATH') or exit('No direct script access allowed');

class Finetuning extends AdminController
{
    /**
     * Display the fine-tuning management page
     */
    public function index()
    {
        if (staff_cant('view', 'settings')) {
            access_denied('OpenAI Settings');
        }

        /** @var OpenAiProvider */
        $provider = AiProviderRegistry::getProvider('openai');

        // Get fine-tuned models
        $fineTunedModels = $provider->getFineTunedModels();

        $currentFineTunedModel = get_option('openai_fine_tuned_model');
        $useFineTuning         = get_option('openai_use_fine_tuning') == '1';
        $finetuningBaseModel   = get_option('openai_fine_tuning_base_model') ?: OpenAiProvider::defaultFineTuningModel();
        $ourFineTunedModel     = get_option('openai_our_fine_tuned_model');
        $lastJobId             = get_option('openai_fine_tuning_last_job_id');
        $lastJob               = [];

        if ($lastJobId) {
            try {
                $lastJob = $provider->retrieveFineTuningJob($lastJobId);

                if (! empty($lastJob['model']) && (empty($ourFineTunedModel) || $ourFineTunedModel !== $lastJob['model'])) {
                    update_option('openai_our_fine_tuned_model', $lastJob['model']);
                    $ourFineTunedModel = $lastJob['model'];
                }
            } catch (Exception $e) {
                // Handle exception if the job is not found or any other error occurs
                log_message('error', 'Error retrieving fine-tuning job: ' . $e->getMessage());
            }
        }

        // Count knowledge base articles
        $this->db->where('active', 1);
        $this->db->where('staff_article', 0);
        $articleCount = $this->db->count_all_results(db_prefix() . 'knowledge_base');

        $this->load->view('openai/finetuning/manage', [
            'title'                          => _l('openai_fine_tuning'),
            'fine_tuned_models'              => $fineTunedModels,
            'current_fine_tuned_model'       => $currentFineTunedModel,
            'our_fine_tuned_model'           => $ourFineTunedModel,
            'use_fine_tuning'                => $useFineTuning,
            'last_job_id'                    => $lastJobId,
            'last_job'                       => $lastJob,
            'article_count'                  => $articleCount,
            'predefined_replies_count'       => $predefinedRepliesCount = $this->db->count_all_results(db_prefix() . 'tickets_predefined_replies'),
            'fine_tuning_base_model'         => $finetuningBaseModel,
            'fine_tuning_models'             => OpenAiProvider::getFineTuningModels(),
            'meets_fine_tuning_requirements' => $meetsRequirements = $articleCount >= 10 || $predefinedRepliesCount >= 10,
            'can_fine_tune'                  => $meetsRequirements && (is_null($lastJob['status'] ?? null) || in_array($lastJob['status'], ['succeeded', 'failed', 'cancelled'])),
        ]);
    }

    public function set_base_model()
    {
        if (staff_cant('edit', 'settings')) {
            access_denied('OpenAI Settings');
        }

        $data = $this->input->post();

        update_option('openai_fine_tuning_base_model', $data['model']);

        echo json_encode([
            'success' => true,
            'message' => _l('settings_updated'),
        ]);
    }

    /**
     * Start a new fine-tuning job
     */
    public function start_job()
    {
        if (staff_cant('edit', 'settings')) {
            access_denied('OpenAI Settings');
        }

        $this->load->library('openai/fine_tuner');
        $result = $this->fine_tuner->startFineTuning();

        echo json_encode($result);
    }

    /**
     * Check the status of a fine-tuning job
     */
    public function check_status()
    {
        if (staff_cant('edit', 'settings')) {
            access_denied('OpenAI Settings');
        }

        $jobId = $this->input->post('job_id');

        $this->load->library('openai/fine_tuner');
        $result = $this->fine_tuner->checkFineTuningStatus($jobId);

        echo json_encode($result);
    }

    /**
     * Toggle the use of fine-tuning
     */
    public function toggle_use()
    {
        if (staff_cant('edit', 'settings')) {
            access_denied('OpenAI Settings');
        }

        $useFineTuning = $this->input->post('use_fine_tuning') === 'true';
        update_option('openai_use_fine_tuning', $useFineTuning ? '1' : '0');

        echo json_encode([
            'success' => true,
            'message' => $useFineTuning ? _l('fine_tuning_enabled') : _l('fine_tuning_disabled'),
        ]);
    }

    /**
     * Set the active fine-tuned model
     */
    public function set_model()
    {
        if (staff_cant('edit', 'settings')) {
            access_denied('OpenAI Settings');
        }

        $modelId = $this->input->post('model_id');
        update_option('openai_fine_tuned_model', $modelId);

        update_option('openai_use_fine_tuning', '1');

        echo json_encode([
            'success' => true,
            'message' => _l('fine_tuned_model_set'),
        ]);
    }

    /**
     * Delete a fine-tuned model
     */
    public function delete_model()
    {
        if (staff_cant('edit', 'settings')) {
            access_denied('OpenAI Settings');
        }

        $modelId = $this->input->post('model_id');

        /** @var OpenAiProvider */
        $provider = AiProviderRegistry::getProvider('openai');
        $result   = $provider->deleteFineTunedModel($modelId);

        echo json_encode([
            'success' => $result,
            'message' => $result ? _l('fine_tuned_model_deleted') : _l('fine_tuned_model_delete_failed'),
        ]);
    }
}
