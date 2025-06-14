<?php

use app\services\ai\AiProviderRegistry;
use Perfexcrm\Openai\OpenAiProvider;

defined('BASEPATH') or exit('No direct script access allowed');

class Fine_tuner
{
    /**
     * CodeIgniter instance.
     */
    private $ci;

    /**
     * Fine_tuner constructor.
     */
    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('knowledge_base_model');
    }

    /**
     * Get all knowledge base articles for fine-tuning.
     */
    public function getKnowledgeBaseData(): array
    {
        if (! hooks()->apply_filters('openai_fine_tuning_knowledge_base', true)) {
            return [];
        }

        // Get all active and non internal knowledge base articles
        $this->ci->db->where('active', 1);
        $this->ci->db->where('staff_article', 0);
        $articles = $this->ci->db->get('knowledge_base')->result_array();

        return array_map(function ($article) {
            return [
                'title'   => $article['subject'],
                'content' => $this->formatArticleContent($article),
            ];
        }, $articles);
    }

    /**
     * Get all predefined replies for fine-tuning.
     */
    public function getPredefinedRepliesData(): array
    {
        if (! hooks()->apply_filters('openai_fine_tuning_predefined_replies', true)) {
            return [];
        }

        $replies = $this->ci->db->get('tickets_predefined_replies')->result_array();

        return array_map(function ($reply) {
            return [
                'title'   => $reply['name'],
                'content' => $this->formatPredefinedReplyContent($reply),
            ];
        }, $replies);
    }

    /**
     * Format article content for fine-tuning.
     */
    private function formatArticleContent(array $article): string
    {
        // Strip HTML tags from the content but preserve structure where possible
        $content = strip_tags($article['description'], '<p><br><a><ul><ol><li><h1><h2><h3><h4><h5>');

        // Add the title at the beginning
        $formattedContent = '# ' . $article['subject'] . "\n\n";

        // Add description
        $formattedContent .= $content;

        // Add more info url
        $url = site_url('knowledge_base/article/' . $article['slug']);
        $formattedContent .= "\n\nFor more information, visit: " . $url;

        return $formattedContent;
    }

    /**
     * Format predefined reply for fine-tuning.
     */
    private function formatPredefinedReplyContent(array $article): string
    {
        // Strip HTML tags from the content but preserve structure where possible
        $content = strip_tags($article['message'], '<p><br><a><ul><ol><li><h1><h2><h3><h4><h5>');

        // Add the title at the beginning
        $formattedContent = '# ' . $article['name'] . "\n\n";

        // Add description
        $formattedContent .= $content;

        return $formattedContent;
    }

    /**
     * Start the fine-tuning process with knowledge base data.
     */
    public function startFineTuning(): array
    {
        $knowledgeBaseData     = $this->getKnowledgeBaseData();
        $predefinedRepliesData = $this->getPredefinedRepliesData();

        if (empty($knowledgeBaseData) && empty($predefinedRepliesData)) {
            return [
                'success' => false,
                'message' => 'Not enough data found for fine-tuning.',
            ];
        }

        /** @var OpenAiProvider */
        $provider = AiProviderRegistry::getProvider('openai');

        // Start the fine-tuning job
        $jobId = $provider->createFineTuningJob([...$knowledgeBaseData, ...$predefinedRepliesData]);

        if (! $jobId) {
            return [
                'success' => false,
                'message' => 'Failed to create fine-tuning job. Check logs for more details.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Fine-tuning job started successfully.',
            'job_id'  => $jobId,
        ];
    }

    /**
     * Check the status of a fine-tuning job.
     */
    public function checkFineTuningStatus(?string $jobId = null): array
    {
        if (! $jobId) {
            $jobId = get_option('openai_fine_tuning_last_job_id');
        }

        if (! $jobId) {
            return [
                'success' => false,
                'message' => 'No fine-tuning job ID found.',
            ];
        }

        /** @var OpenAiProvider */
        $provider = AiProviderRegistry::getProvider('openai');

        // Check job status
        $status = $provider->checkFineTuningStatus($jobId);

        if (isset($status['status']) && $status['status'] === 'error') {
            return [
                'success' => false,
                'message' => 'Error checking fine-tuning job status: ' . ($status['error'] ?? 'Unknown error'),
            ];
        }

        return [
            'success' => true,
            'data'    => $status,
        ];
    }
}
