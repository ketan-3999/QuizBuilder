<?php

/**
 * Front Controller: bootstrap, dispatch, send response (minimal; no business logic).
 * SOLID: Single responsibility = HTTP entry point only.
 */

$baseDir = dirname(__DIR__); 

require_once $baseDir . '/vendor/autoload.php';

use App\Config\Config;
use App\Core\OperationDispatcher;
use App\Http\Request;
use App\Http\ApiResponse;

Config::load($baseDir);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$request   = Request::fromGlobals();
$dispatcher = new OperationDispatcher();

$response = $dispatcher->dispatch($request);

if ($response === null) {
    $response = ApiResponse::error(404, 'Not found');
    http_response_code(404);
} else {
    $code = isset($response['QueryCode']) ? (int) $response['QueryCode'] : 200;
    if ($code >= 400) {
        http_response_code($code);
    }
}

echo json_encode($response);
