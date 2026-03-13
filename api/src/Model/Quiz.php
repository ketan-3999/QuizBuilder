<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Quiz: topic and list of questions. No DB; id can be generated for response.
 */
final class Quiz
{
    /** @var string */
    public $id;
    /** @var string */
    public $topic;
    /** @var Question[] */
    public $questions;
    /** @var string */
    public $createdAt;

    public function __construct(string $id, string $topic, array $questions, ?string $createdAt = null)
    {
        $this->id = $id;
        $this->topic = $topic;
        $this->questions = $questions;
        $this->createdAt = $createdAt ?? date('c');
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'topic' => $this->topic,
            'questions' => array_map(static function (Question $q) { return $q->toArray(); }, $this->questions),
            'createdAt' => $this->createdAt,
        ];
    }

    /** Return quiz without correct answers (for client display). */
    public function toArrayForClient(): array
    {
        return [
            'id' => $this->id,
            'topic' => $this->topic,
            'questions' => array_map(static function (Question $q) { return $q->toArrayForClient(); }, $this->questions),
            'createdAt' => $this->createdAt,
        ];
    }

    /** @return array<int, string> correct answer letter per question index */
    public function getCorrectAnswersByIndex(): array
    {
        $out = [];
        foreach ($this->questions as $i => $q) {
            $out[$i] = $q->correct;
        }
        return $out;
    }

    public static function fromArray(array $data): self
    {
        $questions = [];
        foreach ($data['questions'] ?? [] as $q) {
            $questions[] = Question::fromArray(is_array($q) ? $q : []);
        }
        return new self(
            (string) ($data['id'] ?? ''),
            (string) ($data['topic'] ?? ''),
            $questions,
            isset($data['createdAt']) ? (string) $data['createdAt'] : null
        );
    }
}
