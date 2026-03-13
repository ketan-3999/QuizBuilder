<?php

declare(strict_types=1);

namespace App\Core;

use App\Controller\QuizController;
use App\Http\Request;

/**
 * Dispatches by operation_type. Creates controller internally (no dependency injection).
 */
final class OperationDispatcher
{
    /**
     * Dispatch request to handler. Returns response array or null if no handler.
     * @param Request $request
     * @return array|null array with QueryCode, QueryMsg, data
     */
    public function dispatch(Request $request): ?array
    {
        $operationType = $request->get('operation_type');
        if ($operationType === null || $operationType === '') {
            return null;
        }
        $controller = new QuizController();
        if ($operationType === 'generate_quiz') {
            return $controller->generateQuiz($request);
        }
        if ($operationType === 'submit_quiz') {
            return $controller->submitQuiz($request);
        }
        return null;
    }
}
