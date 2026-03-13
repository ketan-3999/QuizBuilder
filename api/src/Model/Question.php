<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Single multiple-choice question with 4 options (A–D) and one correct answer.
 */
final class Question
{
    /** @var string */
    public $question;
    /** @var array<string, string> keys A, B, C, D */
    public $options;
    /** @var string one of A, B, C, D */
    public $correct;

    public function __construct(string $question, array $options, string $correct)
    {
        $this->question = $question;
        $this->options = $options;
        $this->correct = $correct;
    }

    public function toArray(): array
    {
        return [
            'question' => $this->question,
            'options' => $this->options,
            'correct' => $this->correct,
        ];
    }

    /** Return question + options only (no correct answer) for client. */
    public function toArrayForClient(): array
    {
        return [
            'question' => $this->question,
            'options' => $this->options,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) ($data['question'] ?? ''),
            (array) ($data['options'] ?? []),
            (string) ($data['correct'] ?? 'A')
        );
    }
}
