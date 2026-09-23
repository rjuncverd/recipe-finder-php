<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/RecipeService.php';

header('Content-Type: application/json; charset=utf-8');

$apiKey = getenv('SPOONACULAR_API_KEY') ?: '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Only GET method is allowed.']);
    exit;
}

$query = trim((string)($_GET['name'] ?? ''));
if ($query === '') {
    http_response_code(400);
    echo json_encode(['error' => 'The query parameter "name" is required.']);
    exit;
}

try {
    $client = new SpoonacularClient($apiKey);
    $service = new RecipeService($client);
    $recipe = $service->findByName($query);

    echo json_encode([
        'name' => $recipe['name'],
        'prepTimeMinutes' => $recipe['prepTimeMinutes'],
        'servings' => $recipe['servings'],
        'ingredients' => $recipe['ingredients'],
        'instructions' => $recipe['instructions'],
        'imageUrl' => $recipe['imageUrl'],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (InvalidArgumentException $e) {
    http_response_code(401);
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
