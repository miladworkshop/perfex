<?php

use app\services\ai\AiProviderRegistry;
use app\services\ai\Contracts\AiProviderInterface;

defined('BASEPATH') or exit('No direct script access allowed');

class Ai extends AdminController
{
    private AiProviderInterface $provider;

    public function __construct()
    {
        parent::__construct();

        $this->provider = AiProviderRegistry::getProvider(get_option('ai_provider'));
    }

    public function text_enhancement($enhancementType)
    {
        if (! in_array($enhancementType, ['polite', 'formal', 'friendly'])) {
            show_404('Invalid enhancement type');
        }

        try {
            $enhancedText = $this->provider->enhanceText($this->input->post('text'), $enhancementType);

            echo json_encode([
                'success' => true,
                'message' => $enhancedText,
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
