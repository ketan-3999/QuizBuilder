<?php

declare(strict_types=1);

namespace App\Service;

use App\Config\Config;

/**
 * Calls OpenAI Chat Completions API to generate quiz JSON.
 */
final class OpenAIService implements AIServiceInterface
{
    /** @var string */
    private $apiKey;
    /** @var string */
    private $model;

    public function __construct(?string $apiKey = null, ?string $model = null)
    {
        $this->apiKey = $apiKey ?? Config::get('OPENAI_API_KEY', '');
        $this->model = $model ?? Config::get('OPENAI_MODEL', 'gpt-4o-mini');
    }

    public function generateQuizJson(string $topic): string
    {
        if ($this->apiKey === '' || $this->apiKey === 'sk-your-key-here') {
            throw new \RuntimeException(
                'OpenAI API key is not configured. Copy api/.env.example to api/.env and set OPENAI_API_KEY to your key from https://platform.openai.com/account/api-keys'
            );
        }
        $systemPrompt = 'You are a quiz generator. Respond only with valid JSON, no markdown or extra text.';
        $userPrompt = sprintf(
            'Generate exactly 5 multiple-choice questions about the topic: "%s". '
            . 'Each question must have exactly 4 options labeled A, B, C, and D, with exactly one correct answer. '
            . 'Return a JSON object with this exact structure: '
            . '{"questions":[{"question":"...","options":{"A":"...","B":"...","C":"...","D":"..."},"correct":"A"}]} '
            . 'Use "correct" as the letter (A, B, C, or D) of the correct option. Generate 5 questions now.',
            $topic
        );

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.7,
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            throw new \RuntimeException('OpenAI API request failed: ' . ($response ?: 'curl error'));
        }

        $data = json_decode($response, true);
        $content = $data['choices'][0]['message']['content'] ?? '';
        if ($content === '') {
            throw new \RuntimeException('Empty response from OpenAI');
        }

        // Strip markdown code block if present
        $content = preg_replace('#^```(?:json)?\s*#', '', $content);
        $content = preg_replace('#\s*```\s*$#', '', $content);
        return trim($content);
    }
}
