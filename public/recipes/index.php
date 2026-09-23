<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/RecipeRepositoryInterface.php';
require_once __DIR__ . '/../../src/SpoonacularClient.php';
require_once __DIR__ . '/../../src/RecipeService.php';
require_once __DIR__ . '/../../src/RecipeRequestHandler.php';

header('Content-Type: application/json; charset=utf-8');

$apiKey = getenv('SPOONACULAR_API_KEY') ?: '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$client = new SpoonacularClient($apiKey);
$service = new RecipeService($client);
$handler = new RecipeRequestHandler($service);
$result = $handler->handleRequest($method, $_GET);

http_response_code($result['status']);
printf("%s", json_encode($result['payload'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
