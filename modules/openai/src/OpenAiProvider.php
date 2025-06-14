<?php

namespace Perfexcrm\Openai;

use app\services\ai\Contracts\AiProviderInterface;
use Exception;
use League\HTMLToMarkdown\Converter\TableConverter;
use League\HTMLToMarkdown\HtmlConverter;
use OpenAI;

defined('BASEPATH') or exit('No direct script access allowed');

class OpenAiProvider implements AiProviderInterface
{
    private string $model;
    private string $systemPrompt;
    private OpenAI\Client $client;
    private int $maxToken;
    private bool $useFineTuning;
    private string $fineTunedModel;
    private string $fineTuningBaseModel;
    private static ?array $fineTunedModels = null;

    /**
     * OpenAiProvider constructor.
     */
    public function __construct()
    {
        $this->model               = get_option('openai_model');
        $this->systemPrompt        = get_option('ai_system_prompt');
        $this->maxToken            = intval(get_option('openai_max_token'));
        $this->useFineTuning       = get_option('openai_use_fine_tuning') == '1';
        $this->fineTunedModel      = get_option('openai_fine_tuned_model') ?: '';
        $this->fineTuningBaseModel = get_option('openai_fine_tuning_base_model') ?: static::defaultFineTuningModel();

        $this->client = OpenAI::factory()
            ->withApiKey(get_option('openai_api_key'))
            ->withHttpHeader('Content-Type', 'application/json')
            ->withHttpHeader('Accept', 'application/json')
            ->make();
    }

    /**
     * Get the list of available models for selection.
     */
    public static function getModels(): array
    {
        return hooks()->apply_filters('openai_models', [
            [
                'id'   => 'gpt-3.5-turbo',
                'name' => 'GPT-3.5 Turbo',
            ],
            [
                'id'   => 'gpt-4o',
                'name' => 'GPT-4o',
            ],
            [
                'id'   => 'gpt-4o-mini',
                'name' => 'GPT-4o Mini',
            ],
            [
                'id'   => 'o1-mini',
                'name' => 'o1 Mini',
            ],
        ]);
    }

    /**
     * Get the default fine-tuning model.
     */
    public static function defaultFineTuningModel(): string
    {
        return 'gpt-4o-mini-2024-07-18';
    }

    /**
     * Get the list of fine-tuning models available for selection.
     */
    public static function getFineTuningModels(): array
    {
        return hooks()->apply_filters('openai_fine_tuning_models', [
            [
                'id'   => 'gpt-4.1-2025-04-14',
                'name' => 'GPT-4.1 (2025-04-14)',
            ],
            [
                'id'   => 'gpt-4.1-mini-2025-04-14',
                'name' => 'GPT-4.1 Mini (2025-04-14)',
            ],
            [
                'id'   => 'gpt-4o-2024-08-06',
                'name' => 'GPT-4o (2024-08-06)',
            ],
            [
                'id'   => 'gpt-4o-mini-2024-07-18',
                'name' => 'GPT-4o Mini (2024-07-18)',
            ],
            [
                'id'   => 'gpt-4-0613',
                'name' => 'GPT-4 (0613)',
            ],
            [
                'id'   => 'gpt-3.5-turbo-0125',
                'name' => 'GPT-3.5 Turbo (0125)',
            ],
            [
                'id'   => 'gpt-3.5-turbo-1106',
                'name' => 'GPT-3.5 Turbo (1106)',
            ],
            [
                'id'   => 'gpt-3.5-turbo-0613',
                'name' => 'GPT-3.5 Turbo (0613)',
            ],
        ]);
    }

    /**
     * Get the name of the AI provider.
     */
    public function getName(): string
    {
        return 'OpenAI';
    }

    /**
     * Initiate a chat with the OpenAI API using the provided prompt.
     *
     * @param mixed $prompt
     */
    public function chat($prompt): string
    {
        // If fine-tuning is enabled and we have a fine-tuned model, use it
        $model = $this->useFineTuning && ! empty($this->fineTunedModel)
            ? $this->fineTunedModel
            : $this->model;

        $response = $this->client->chat()->create([
            'model'    => $model,
            'store'    => true,
            'messages' => [
                ['role' => 'developer', 'content' => $this->systemPrompt],
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => $this->maxToken,
        ]);

        return rtrim(ltrim($response->choices[0]->message->content ?? '', '```html'), '```');
    }

    /**
     * Create a fine-tuning job using the knowledge base content.
     *
     * @param array{title: string, content: string} $trainingData
     */
    public function createFineTuningJob(array $trainingData): ?string
    {
        if (empty($trainingData)) {
            return null;
        }

        $this->deletePreviousFineTunedModel();

        try {
            // Format knowledge base data into training examples
            $trainingData = $this->formatTrainingData($trainingData);

            // Create a temporary file with the training data
            $tempFilePath = sys_get_temp_dir() . '/pcrm_training_' . time() . '.jsonl';
            file_put_contents($tempFilePath, $trainingData);

            // Create a file with the training data
            $fileResponse = $this->client->files()->upload([
                'purpose' => 'fine-tune',
                'file'    => fopen($tempFilePath, 'r'),
            ]);

            // Remove the temporary file
            @unlink($tempFilePath);

            // Start a fine-tuning job using the selected fine-tuning base model
            $fineTuningResponse = $this->client->fineTuning()->createJob([
                'training_file' => $fileResponse->id,
                'model'         => $this->fineTuningBaseModel,
                'suffix'        => 'pcrm-' . date('Ymd'),
            ]);

            // Save the fine-tuning job ID for later reference
            update_option('openai_fine_tuning_last_job_id', $fineTuningResponse->id);
            update_option('openai_last_fine_tuning_file_id', $fileResponse->id);

            return $fineTuningResponse->id;
        } catch (Exception $e) {
            log_activity('OpenAI Fine-tuning Error: ' . $e->getMessage());

            if (isset($tempFilePath)) {
                // Clean up the temporary file if it exists
                @unlink($tempFilePath);
            }

            return null;
        }
    }

    /**
     * Format knowledge base data into the required training format for fine-tuning.
     */
    private function formatTrainingData(array $trainingData): string
    {
        $formattedData = [];

        foreach ($trainingData as $item) {
            $formattedData[] = [
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt],
                    ['role' => 'user', 'content' => 'Tell me about ' . $item['title']],
                    ['role' => 'assistant', 'content' => $item['content']],
                ],
            ];
        }

        $formattedData = hooks()->apply_filters(
            'openai_fine_tuning_training_data',
            $formattedData,
            $this->systemPrompt
        );

        foreach ($formattedData as $key => $data) {
            if (empty($data)) {
                unset($formattedData[$key]);
            } else {
                $formattedData[$key] = json_encode($data, JSON_UNESCAPED_UNICODE);
            }
        }

        return implode("\n", array_values($formattedData));
    }

    /**
     * Retrieve the fine-tuning job.
     */
    public function retrieveFineTuningJob(string $jobId): array
    {
        $job = $this->client->fineTuning()->retrieveJob($jobId);

        return [
            'status'      => $job->status,
            'model'       => $job->fineTunedModel ?? null,
            'created_at'  => $job->createdAt,
            'finished_at' => $job->finishedAt ?? null,
            'error'       => $job->error ?? null,
        ];
    }

    /**
     * Check the status of a fine-tuning job.
     */
    public function checkFineTuningStatus(string $jobId): array
    {
        try {
            $job = $this->retrieveFineTuningJob($jobId);

            if ($job['status'] === 'succeeded' && ! empty($job['model']) && empty($this->fineTunedModel)) {
                // If the job completed successfully, save the fine-tuned model ID
                update_option('openai_fine_tuned_model', $job['model']);
                update_option('openai_use_fine_tuning', '1');
            }

            return $job;
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'error'  => $e->getMessage(),
            ];
        }
    }

    /**
     * List all fine-tuned models for the current account.
     */
    public function getFineTunedModels(): array
    {
        if (! is_null(static::$fineTunedModels)) {
            return static::$fineTunedModels;
        }

        try {
            $response        = $this->client->models()->list();
            $fineTunedModels = [];

            foreach ($response->data as $model) {
                // Fine-tuned models typically have a format like ft:gpt-3.5-turbo:org:custom_suffix:id
                if (strpos($model->id, 'ft:') === 0) {
                    $fineTunedModels[] = [
                        'id'         => $model->id,
                        'created_at' => $model->created,
                        'owned_by'   => $model->ownedBy,
                        'is_our'     => static::isOurFineTunedModel($model->id),
                    ];
                }
            }

            // Sort by id that contains "pcrm-", should be considered on top, and sorted by created_at
            usort($fineTunedModels, function ($a, $b) {
                if (strpos($a['id'], 'pcrm-') !== false && strpos($b['id'], 'pcrm-') === false) {
                    return -1;
                }
                if (strpos($a['id'], 'pcrm-') === false && strpos($b['id'], 'pcrm-') !== false) {
                    return 1;
                }

                return $b['created_at'] <=> $a['created_at'];
            });

            return static::$fineTunedModels = $fineTunedModels;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get the list of fine-tuned models that belong to us.
     */
    public function getOurFineTunedModels(): array
    {
        $fineTunedModels    = $this->getFineTunedModels();
        $ourFineTunedModels = [];

        foreach ($fineTunedModels as $model) {
            if ($model['is_our']) {
                $ourFineTunedModels[] = $model;
            }
        }

        return $ourFineTunedModels;
    }

    /**
     * Delete a fine-tuned model.
     */
    public function deleteFineTunedModel(string $modelId): bool
    {
        if (! static::isOurFineTunedModel($modelId)) {
            return false;
        }

        try {
            $this->client->models()->delete($modelId);

            // If we deleted the currently selected fine-tuned model, reset the settings
            if ($this->fineTunedModel === $modelId) {
                update_option('openai_fine_tuned_model', '');
                update_option('openai_use_fine_tuning', '0');
            }

            if (get_option('openai_our_fine_tuned_model') === $modelId) {
                update_option('openai_our_fine_tuned_model', '');

                $fileId = get_option('openai_last_fine_tuning_file_id');
                if (! empty($fileId)) {
                    try {
                        $this->client->files()->delete($fileId);
                    } catch (Exception $e) {
                        log_activity('OpenAI Fine-tuned Model File Deletion Error: ' . $e->getMessage());
                    }
                }
            }

            return true;
        } catch (Exception $e) {
            log_activity('OpenAI Fine-tuned Model Deletion Error: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Delete all previous fine-tuned models that belong to us.
     */
    protected function deletePreviousFineTunedModel(): void
    {
        $fineTunedModels = $this->getOurFineTunedModels();

        foreach ($fineTunedModels as $model) {
            $this->deleteFineTunedModel($model['id']);
        }
    }

    /**
     * Check if the model is a fine-tuned model that belongs to us.
     */
    public static function isOurFineTunedModel(string $model): bool
    {
        return strpos($model, 'pcrm-') !== false;
    }

    /**
     * Enhance the given text to be more polite, formal, or friendly.
     */
    public function enhanceText(string $text, string $enhancementType): string
    {
        $converter = new HtmlConverter();
        $converter->getConfig()->setOption('strip_tags', true);
        $converter->getEnvironment()->addConverter(new TableConverter());

        $prompt = <<<TICKET
                    Enhance the following text to be more {$enhancementType}. Only return the enhanced text without any explanations or introductions, the text should be TinyMCE 6 compatible HTML format:\n\n
                
                    {$converter->convert($text)}
            TICKET;

        $result = $this->chat(
            hooks()->apply_filters('before_ai_tickets_enhance_text', $prompt, $text, $enhancementType)
        );

        // Do not wrap the output in a `<p>` tag if the input is a single paragraph or a partial sentence.
        // Return plain inline HTML in those cases.
        if (startsWith($result, '<p>') && endsWith($result, '</p>') && substr_count($result, '<p>') === 1) {
            $result = strip_tags($result, '<strong><em><u><span><a><b><i>');
        }

        return $result;
    }
}
