<?php

declare(strict_types=1);

final class RecipeRequestHandler
{
    public function __construct(private RecipeService $service)
    {
    }

    public function handleRequest(string $method, array $query): array
    {
        if ($method !== 'GET') {
            return [
                'status' => 405,
                'payload' => ['error' => 'Only GET method is allowed.'],
            ];
        }

        $name = trim((string)($query['name'] ?? ''));
        if ($name === '') {
            return [
                'status' => 400,
                'payload' => ['error' => 'The query parameter "name" is required.'],
            ];
        }

        try {
            $recipe = $this->service->findByName($name);

            return [
                'status' => 200,
                'payload' => [
                    'name' => $recipe['name'],
                    'prepTimeMinutes' => $recipe['prepTimeMinutes'],
                    'servings' => $recipe['servings'],
                    'ingredients' => $recipe['ingredients'],
                    'instructions' => $recipe['instructions'],
                    'imageUrl' => $recipe['imageUrl'],
                ],
            ];
        } catch (InvalidArgumentException $e) {
            return [
                'status' => 401,
                'payload' => ['error' => $e->getMessage()],
            ];
        } catch (Throwable $e) {
            return [
                'status' => 500,
                'payload' => ['error' => $e->getMessage()],
            ];
        }
    }
}
