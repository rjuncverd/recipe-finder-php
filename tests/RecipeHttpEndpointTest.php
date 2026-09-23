<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/RecipeRepositoryInterface.php';
require_once __DIR__ . '/../src/RecipeService.php';
require_once __DIR__ . '/../src/RecipeRequestHandler.php';

use PHPUnit\Framework\TestCase;

final class RecipeHttpEndpointTest extends TestCase
{
    public function testEndpointRequiresNameParameter(): void
    {
        $service = new RecipeService(new class implements RecipeRepositoryInterface {
            public function searchRecipeByName(string $name): ?array
            {
                return null;
            }
        });

        $handler = new RecipeRequestHandler($service);
        $result = $handler->handleRequest('GET', []);

        $this->assertSame(400, $result['status']);
        $this->assertSame(['error' => 'The query parameter "name" is required.'], $result['payload']);
    }

    public function testEndpointRejectsUnsupportedMethods(): void
    {
        $service = new RecipeService(new class implements RecipeRepositoryInterface {
            public function searchRecipeByName(string $name): ?array
            {
                return null;
            }
        });

        $handler = new RecipeRequestHandler($service);
        $result = $handler->handleRequest('POST', ['name' => 'pasta']);

        $this->assertSame(405, $result['status']);
        $this->assertSame(['error' => 'Only GET method is allowed.'], $result['payload']);
    }
}
