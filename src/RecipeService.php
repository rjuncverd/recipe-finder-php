<?php

declare(strict_types=1);

require_once __DIR__ . '/RecipeRepositoryInterface.php';

final class RecipeService
{
    public function __construct(private RecipeRepositoryInterface $repository)
    {
    }

    public function findByName(string $queryName): array
    {
        $recipe = $this->repository->searchRecipeByName($queryName);

        if ($recipe === null) {
            throw new RuntimeException('No recipe found for the provided name.');
        }

        $ingredients = [];
        foreach ($recipe['extendedIngredients'] ?? [] as $ingredient) {
            $name = $ingredient['original'] ?? $ingredient['name'] ?? null;
            if (is_string($name) && trim($name) !== '') {
                $ingredients[] = trim($name);
            }
        }

        $instructions = [];
        foreach ($recipe['analyzedInstructions'] ?? [] as $instructionGroup) {
            foreach ($instructionGroup['steps'] ?? [] as $step) {
                $stepText = trim((string)($step['step'] ?? ''));
                if ($stepText !== '') {
                    $instructions[] = $stepText;
                }
            }
        }

        if ($instructions === []) {
            $recipeInstructions = trim((string)($recipe['instructions'] ?? ''));
            if ($recipeInstructions !== '') {
                $instructions = [$recipeInstructions];
            }
        }

        return [
            'name' => $recipe['title'] ?? null,
            'prepTimeMinutes' => $recipe['readyInMinutes'] ?? $recipe['prepTimeMinutes'] ?? null,
            'servings' => $recipe['servings'] ?? $recipe['servingsNumber'] ?? null,
            'ingredients' => $ingredients,
            'instructions' => $instructions ?: null,
            'imageUrl' => $recipe['image'] ?? $recipe['imageUrl'] ?? null,
        ];
    }
}
