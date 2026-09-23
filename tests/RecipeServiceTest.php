<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/RecipeRepositoryInterface.php';
require_once __DIR__ . '/../src/RecipeService.php';

use PHPUnit\Framework\TestCase;

final class RecipeServiceTest extends TestCase
{
    public function testFindByNameReturnsNormalizedRecipeData(): void
    {
        $repository = new class implements RecipeRepositoryInterface {
            public function searchRecipeByName(string $name): ?array
            {
                return [
                    'title' => 'Pasta Primavera',
                    'readyInMinutes' => 30,
                    'servings' => 4,
                    'image' => 'https://example.com/pasta.jpg',
                    'extendedIngredients' => [
                        ['original' => '200 g pasta'],
                        ['original' => '1 zucchini'],
                        ['original' => '2 tomatoes'],
                    ],
                    'analyzedInstructions' => [
                        [
                            'steps' => [
                                ['step' => 'Cook the pasta.'],
                                ['step' => 'Mix with vegetables.'],
                            ],
                        ],
                    ],
                ];
            }
        };

        $service = new RecipeService($repository);

        $result = $service->findByName('pasta');

        $this->assertSame('Pasta Primavera', $result['name']);
        $this->assertSame(30, $result['prepTimeMinutes']);
        $this->assertSame(4, $result['servings']);
        $this->assertSame([
            '200 g pasta',
            '1 zucchini',
            '2 tomatoes',
        ], $result['ingredients']);
        $this->assertSame([
            'Cook the pasta.',
            'Mix with vegetables.',
        ], $result['instructions']);
        $this->assertSame('https://example.com/pasta.jpg', $result['imageUrl']);
    }

    public function testFindByNameThrowsWhenRepositoryReturnsNull(): void
    {
        $repository = new class implements RecipeRepositoryInterface {
            public function searchRecipeByName(string $name): ?array
            {
                return null;
            }
        };

        $service = new RecipeService($repository);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No recipe found for the provided name.');

        $service->findByName('unknown recipe');
    }

    public function testFindByNameFallsBackToInstructionsStringWhenStepsAreNotAvailable(): void
    {
        $repository = new class implements RecipeRepositoryInterface {
            public function searchRecipeByName(string $name): ?array
            {
                return [
                    'title' => 'Simple Soup',
                    'readyInMinutes' => 15,
                    'servings' => 2,
                    'imageUrl' => 'https://example.com/soup.jpg',
                    'extendedIngredients' => [
                        ['original' => '2 carrots'],
                    ],
                    'instructions' => 'Boil water. Add carrots.',
                    'analyzedInstructions' => [],
                ];
            }
        };

        $service = new RecipeService($repository);

        $result = $service->findByName('soup');

        $this->assertSame(['Boil water. Add carrots.'], $result['instructions']);
    }
}
