<?php

declare(strict_types=1);

final class SpoonacularClient
{
    public function __construct(private string $apiKey)
    {
    }

    public function searchRecipeByName(string $name): ?array
    {
        if ($this->apiKey === '') {
            throw new InvalidArgumentException('SPOONACULAR_API_KEY is required. Set the environment variable before calling the endpoint.');
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

        // NOTA: Si la búsqueda devuelve múltiples resultados, devolver el primero de ellos.
        // Aunque con number => 1 ya solo esperamos un resultado, verificamos que exista.
        $firstResult = $data['results'][0] ?? null;
        $recipeId = $firstResult['id'] ?? null;

        if (!is_int($recipeId) && !ctype_digit((string) $recipeId)) {
            return $firstResult;
        }

        $detailUrl = 'https://api.spoonacular.com/recipes/' . (int) $recipeId . '/information?' . http_build_query([
            'apiKey' => $this->apiKey,
            'includeNutrition' => false,
        ]);

        $detail = $this->fetchJson($detailUrl);

        return is_array($detail) ? $detail : $firstResult;
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
