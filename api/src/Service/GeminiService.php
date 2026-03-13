<?php

declare(strict_types=1);

namespace App\Service;

use App\Config\Config;

/**
 * Calls Google Gemini API to generate quiz JSON.
 */
final class GeminiService implements AIServiceInterface
{
    /** @var string */
    private $apiKey;
    /** @var string */
    private $model;

    public function __construct(?string $apiKey = null, ?string $model = null)
    {
        $this->apiKey = $apiKey ?? Config::get('GEMINI_API_KEY', '');
        $this->model = $model ?? Config::get('GEMINI_MODEL', 'gemini-2.5-flash');
    }

    public function generateQuizJson(string $topic): string
    {
        if ($this->apiKey === '' || $this->apiKey === 'your-gemini-key-here') {
            throw new \RuntimeException(
                'Gemini API key is not configured. Copy api/.env.example to api/.env and set GEMINI_API_KEY from https://aistudio.google.com/app/apikey'
            );
        }

        $prompt = 'You are a quiz generator. Respond only with valid JSON, no markdown or extra text. '
            . sprintf(
                'Generate exactly 5 multiple-choice questions about the topic: "%s". '
                . 'Each question must have exactly 4 options labeled A, B, C, and D, with exactly one correct answer. '
                . 'Return a JSON object with this exact structure: '
                . '{"questions":[{"question":"...","options":{"A":"...","B":"...","C":"...","D":"..."},"correct":"A"}]} '
                . 'Use "correct" as the letter (A, B, C, or D) of the correct option. Generate 5 questions now.',
                $topic
            );

        $payload = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array('text' => $prompt),
                    ),
                ),
            ),
            'generationConfig' => array(
                'temperature' => 0.7,
            ),
        );

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent';
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'x-goog-api-key: ' . $this->apiKey,
            ),
            CURLOPT_POSTFIELDS => json_encode($payload),
        ));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            $err = is_string($response) ? $response : 'curl error';
            throw new \RuntimeException('Gemini API request failed: ' . $err);
        }

        $data = json_decode($response, true);
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if ($text === '') {
            throw new \RuntimeException('Empty response from Gemini');
        }

        $text = preg_replace('#^```(?:json)?\s*#', '', $text);
        $text = preg_replace('#\s*```\s*$#', '', $text);
        return trim($text);
    }
}
