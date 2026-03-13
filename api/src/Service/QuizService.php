<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Question;
use App\Model\Quiz;
use App\Model\QuizResult;

final class QuizService
{
    /** @var AIServiceInterface */
    private $aiService;

    public function __construct(AIServiceInterface $aiService)
    {
        $this->aiService = $aiService;
    }

    public function generateQuiz(string $topic): Quiz
    {
        $json = $this->aiService->generateQuizJson($topic);
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['questions']) || !is_array($data['questions'])) {
            throw new \RuntimeException('Invalid quiz JSON from AI: ' . json_last_error_msg());
        }

        $questions = [];
        foreach (array_slice($data['questions'], 0, 5) as $q) {
            $options = $q['options'] ?? [];
            if (!isset($options['A'], $options['B'], $options['C'], $options['D'], $q['correct'])) {
                continue;
            }
            $correct = strtoupper((string) $q['correct']);
            if (!in_array($correct, ['A', 'B', 'C', 'D'], true)) {
                $correct = 'A';
            }
            $questions[] = new Question(
                (string) ($q['question'] ?? ''),
                ['A' => (string) $options['A'], 'B' => (string) $options['B'], 'C' => (string) $options['C'], 'D' => (string) $options['D']],
                $correct
            );
        }

        if (count($questions) < 5) {
            throw new \RuntimeException('AI did not return 5 valid questions');
        }

        $id = 'quiz_' . bin2hex(random_bytes(8));
        return new Quiz($id, $topic, $questions);
    }

    /**
     * @param array<int, string> $userAnswers map question index (0-4) to selected letter (A-D)
     */
    public function scoreQuiz(Quiz $quiz, array $userAnswers): QuizResult
    {
        $details = [];
        $score = 0;
        foreach ($quiz->questions as $i => $question) {
            $userAnswer = strtoupper((string) ($userAnswers[$i] ?? ''));
            $isCorrect = $userAnswer === $question->correct;
            if ($isCorrect) {
                $score++;
            }
            $details[$i] = [
                'correctAnswer' => $question->correct,
                'userAnswer' => $userAnswer ?: '-',
                'isCorrect' => $isCorrect,
            ];
        }
        return new QuizResult($score, count($quiz->questions), $details);
    }

    /**
     * Score using server-stored correct answers (no quiz payload with answers needed).
     * @param array<int, string> $correctAnswersByIndex
     * @param array<int, string> $userAnswers
     * @return QuizResult
     */
    public function scoreQuizWithStoredAnswers(array $correctAnswersByIndex, array $userAnswers): QuizResult
    {
        $details = [];
        $score = 0;
        $total = count($correctAnswersByIndex);
        foreach ($correctAnswersByIndex as $i => $correctLetter) {
            $userAnswer = strtoupper((string) ($userAnswers[$i] ?? ''));
            $isCorrect = $userAnswer === $correctLetter;
            if ($isCorrect) {
                $score++;
            }
            $details[$i] = [
                'correctAnswer' => $correctLetter,
                'userAnswer' => $userAnswer ?: '-',
                'isCorrect' => $isCorrect,
            ];
        }
        return new QuizResult($score, $total, $details);
    }
}
