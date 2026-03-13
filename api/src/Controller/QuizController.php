<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\ApiResponse;
use App\Http\Request;
use App\Service\AIServiceFactory;
use App\Service\QuizService;

/**
 * Handles quiz operations only (SRP). Returns response arrays; no I/O.
 * Creates its own QuizService (no dependency injection).
 */
final class QuizController
{
    /** @var QuizService */
    private $quizService;

    public function __construct()
    {
        $baseDir = dirname(dirname(__DIR__));
        $this->quizService = new QuizService(AIServiceFactory::create($baseDir));
    }

    /**
     * Generate quiz by topic. Returns array with QueryCode, QueryMsg, data.
     * @param Request $request
     * @return array{QueryCode: int, QueryMsg: string, data: mixed}
     */
    public function generateQuiz(Request $request): array
    {
        $topic = trim((string) $request->get('topic', ''));
        if ($topic === '') {
            return ApiResponse::error(400, 'Topic is required');
        }

        try {
            $quiz = $this->quizService->generateQuiz($topic);
            $_SESSION['quiz_answers'][$quiz->id] = $quiz->getCorrectAnswersByIndex();
            return ApiResponse::success(200, 'Quiz generated successfully!', $quiz->toArrayForClient());
        } catch (\Exception $ex) {
            $code = $ex->getCode() ?: 502;
            return ApiResponse::exception((int) $code, 'Failed to generate quiz: ' . $ex->getMessage());
        }
    }

    /**
     * Submit quiz answers and return score. Returns array with QueryCode, QueryMsg, data.
     * @param Request $request
     * @return array{QueryCode: int, QueryMsg: string, data: mixed}
     */
    public function submitQuiz(Request $request): array
    {
        $quizId  = $request->get('quizId');
        $answers = $request->get('answers');
        if (empty($quizId) || !is_array($answers)) {
            return ApiResponse::error(400, 'quizId and answers are required');
        }
        $quizId = (string) $quizId;
        $stored = isset($_SESSION['quiz_answers'][$quizId]) ? $_SESSION['quiz_answers'][$quizId] : null;
        if (!is_array($stored)) {
            return ApiResponse::error(404, 'Quiz not found or expired. Please generate a new quiz.');
        }

        try {
            $result = $this->quizService->scoreQuizWithStoredAnswers($stored, $answers);
            unset($_SESSION['quiz_answers'][$quizId]);
            return ApiResponse::success(200, 'Successfully submitted quiz!', $result->toArray());
        } catch (\Exception $ex) {
            $code = $ex->getCode() ?: 500;
            return ApiResponse::exception((int) $code, 'Internal Server Error: ' . $ex->getMessage());
        }
    }
}
