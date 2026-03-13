<?php

declare(strict_types=1);

namespace App\Service;

use App\Config\Config;

/**
 * Factory for AI service implementations (Open/Closed: add new providers without changing callers).
 * Dependency Inversion: callers depend on AIServiceInterface, factory creates concrete implementation.
 */
final class AIServiceFactory
{
    /**
     * Create AI service based on config. Default: Gemini.
     * @param string $baseDir Base directory for Config::load
     * @return AIServiceInterface
     */
    public static function create(string $baseDir): AIServiceInterface
    {
        Config::load($baseDir);
        $provider = strtolower(Config::get('AI_PROVIDER', 'gemini'));
        if ($provider === 'openai') {
            return new OpenAIService();
        }
        if ($provider === 'gemini') {
            return new GeminiService();
        }
        return new GeminiService();
    }
}
