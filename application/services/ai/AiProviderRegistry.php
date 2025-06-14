<?php

namespace app\services\ai;

use app\services\ai\Contracts\AiProviderInterface;
use RuntimeException;

defined('BASEPATH') or exit('No direct script access allowed');

class AiProviderRegistry
{
    /**
     * @var array<string, AiProviderInterface>
     */
    private static array $providers = [];

    /**
     * Register a new AI provider with a unique name.
     *
     * @param string $identifier
     * @param AiProviderInterface $provider
     */
    public static function registerProvider(string $identifier, AiProviderInterface $provider): void
    {
        self::$providers[$identifier] = $provider;
    }

    /**
     * Retrieve an AI provider by its name.
     *
     * @param string $identifier
     * @return AiProviderInterface
     */
    public static function getProvider(string $identifier): AiProviderInterface
    {
        if (!isset(self::$providers[$identifier])) {
            throw new RuntimeException("AI provider not found: $identifier");
        }

        return self::$providers[$identifier];
    }

    /**
     * Get all registered providers.
     *
     * @return array<int, array{identifier: string, provider: AiProviderInterface}>
     */
    public static function getAllProviders(): array
    {
        return collect(self::$providers)
            ->mapWithKeys(function (AiProviderInterface $provider, string $identifier) {
                return [$identifier => [
                    'id' => $identifier,
                    'name' => $provider->getName(),
                    'provider' => $provider,
                ]
                ];
            })
            ->toArray();
    }
}

