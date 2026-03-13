<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Result of submitting a quiz: score and per-question correct answers / user answers.
 */
final class QuizResult
{
    /** @var int */
    public $score;
    /** @var int */
    public $total;
    /** @var array<int, array{correctAnswer: string, userAnswer: string, isCorrect: bool}> */
    public $details;

    public function __construct(int $score, int $total, array $details)
    {
        $this->score = $score;
        $this->total = $total;
        $this->details = $details;
    }

    public function toArray(): array
    {
        return [
            'score' => $this->score,
            'total' => $this->total,
            'details' => $this->details,
        ];
    }
}
