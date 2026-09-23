<?php

declare(strict_types=1);

require_once __DIR__ . '/RecipeRepositoryInterface.php';
require_once __DIR__ . '/FileCache.php';

final class SpoonacularClient implements RecipeRepositoryInterface
{
    public function __construct(
        private string $apiKey,
        private ?FileCache $cache = null,
        private int $cacheTtlSeconds = 3600
    ) {
        $this->cache ??= new FileCache();
    }

    public function searchRecipeByName(string $name): ?array
    {
        if ($this->apiKey === '') {
            throw new InvalidArgumentException('SPOONACULAR_API_KEY is required. Set the environment variable before calling the endpoint.');
        }

        $cacheKey = 'recipe:' . strtolower(trim($name));
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $searchUrl = 'https://api.spoonacular.com/recipes/complexSearch?' . http_build_query([
            'query' => $name,
            'number' => 1,
            'apiKey' => $this->apiKey,
            'addRecipeInformation' => true,
            'fillIngredients' => true,
        ]);

        $data = $this->fetchJson($searchUrl);

        if (!isset($data['results']) || !is_array($data['results'])) {
            return null;
        }

        $firstResult = $data['results'][0] ?? null;
        $recipeId = $firstResult['id'] ?? null;

        if (!is_int($recipeId) && !ctype_digit((string) $recipeId)) {
            $this->cache->set($cacheKey, $firstResult, $this->cacheTtlSeconds);
            return $firstResult;
        }

        $detailUrl = 'https://api.spoonacular.com/recipes/' . (int) $recipeId . '/information?' . http_build_query([
            'apiKey' => $this->apiKey,
            'includeNutrition' => false,
        ]);

        $detail = $this->fetchJson($detailUrl);
        $result = is_array($detail) ? $detail : $firstResult;

        $this->cache->set($cacheKey, $result, $this->cacheTtlSeconds);

        return $result;
    }

    private function fetchJson(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "Accept: application/json\r\nUser-Agent: recipe-finder-php/1.0\r\n",
                'timeout' => 15,
            ],
        ]);

        $response = file_get_contents($url, false, $context);
        if ($response === false) {
            throw new RuntimeException('Unable to reach Spoonacular API. Please verify the API key and network access.');
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            throw new RuntimeException('Spoonacular returned an invalid response.');
        }

        if (($data['status'] ?? null) === 'failure' || (($data['code'] ?? null) >= 400)) {
            $message = $data['message'] ?? 'Spoonacular API request failed.';
            throw new RuntimeException($message);
        }

        return $data;
    }
}
