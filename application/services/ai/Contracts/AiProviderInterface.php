<?php

namespace app\services\ai\Contracts;

defined('BASEPATH') or exit('No direct script access allowed');

interface AiProviderInterface
{
    public function getName(): string;

    public static function getModels(): array;

    public function chat($prompt): string;

    public function enhanceText(string $text, string $type): string;
}
