<?php

declare(strict_types=1);

interface RecipeRepositoryInterface
{
    public function searchRecipeByName(string $name): ?array;
}
