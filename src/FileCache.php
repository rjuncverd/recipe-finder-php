<?php

declare(strict_types=1);

final class FileCache
{
    public function __construct(
        private string $directory = __DIR__ . '/../storage/cache',
        private int $ttlSeconds = 3600
    ) {
        if (!is_dir($this->directory) && !mkdir($this->directory, 0777, true) && !is_dir($this->directory)) {
            throw new RuntimeException(sprintf('Unable to create cache directory: %s', $this->directory));
        }
    }

    public function get(string $key): mixed
    {
        $path = $this->pathForKey($key);
        if (!is_file($path)) {
            return null;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        if (!is_array($data) || !isset($data['expiresAt'], $data['value'])) {
            @unlink($path);
            return null;
        }

        if ((int) $data['expiresAt'] < time()) {
            @unlink($path);
            return null;
        }

        return $data['value'];
    }

    public function set(string $key, mixed $value, ?int $ttlSeconds = null): bool
    {
        $payload = [
            'expiresAt' => time() + ($ttlSeconds ?? $this->ttlSeconds),
            'value' => $value,
        ];

        $json = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $path = $this->pathForKey($key);

        return (bool) file_put_contents($path, $json, LOCK_EX);
    }

    public function delete(string $key): bool
    {
        $path = $this->pathForKey($key);
        if (!is_file($path)) {
            return true;
        }

        return unlink($path);
    }

    private function pathForKey(string $key): string
    {
        $safeKey = md5($key);
        return rtrim($this->directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeKey . '.json';
    }
}
