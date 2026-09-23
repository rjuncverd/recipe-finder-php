<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

if ($uri === '/' || $uri === '') {
    echo json_encode([
        'message' => 'API disponible. Usa GET /recipes?name=...',
        'swagger' => '/swagger.html'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

http_response_code(404);
echo json_encode([
    'error' => 'Route not found. Use GET /recipes?name=...',
    'swagger' => '/swagger.html'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
