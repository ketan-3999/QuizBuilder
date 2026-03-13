<?php

declare(strict_types=1);

namespace App\Service;

interface AIServiceInterface
{
    /**
     * Generate raw JSON string for a quiz (5 questions, 4 options A–D, one correct each).
     */
    public function generateQuizJson(string $topic): string;
}
