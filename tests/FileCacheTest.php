<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/FileCache.php';

use PHPUnit\Framework\TestCase;

final class FileCacheTest extends TestCase
{
    public function testSetAndGetReturnsCachedValue(): void
    {
        $cacheDir = sys_get_temp_dir() . '/recipe-cache-test-' . uniqid('', true);
        $cache = new FileCache($cacheDir, 60);

        $cache->set('user:1', ['name' => 'Alice']);

        $this->assertSame(['name' => 'Alice'], $cache->get('user:1'));

        $cache->delete('user:1');
        @rmdir($cacheDir);
    }

    public function testExpiredValueIsNotReturned(): void
    {
        $cacheDir = sys_get_temp_dir() . '/recipe-cache-test-' . uniqid('', true);
        $cache = new FileCache($cacheDir, 1);

        $cache->set('user:2', ['name' => 'Bob'], 1);
        sleep(2);

        $this->assertNull($cache->get('user:2'));

        @rmdir($cacheDir);
    }
}
