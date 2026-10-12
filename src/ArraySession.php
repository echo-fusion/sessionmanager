<?php

declare(strict_types=1);

namespace EchoFusion\SessionManager;

use EchoFusion\Contracts\SessionManager\SessionInterface;
use EchoFusion\SessionManager\Exceptions\SessionException;
use function array_key_exists;

/**
 * In-memory session. Useful for tests and for CLI/queue code that has no HTTP session.
 */
final class ArraySession implements SessionInterface
{
    private const FLASH_KEY = '_flash';

    private bool $active = false;

    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function start(): void
    {
        if ($this->active) {
            throw new SessionException('Session has already been started.');
        }

        $this->active = true;
    }

    public function save(): void
    {
        $this->active = false;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->assertActive();

        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    public function put(string $key, mixed $value): void
    {
        $this->assertActive();

        $this->data[$key] = $value;
    }

    public function has(string $key): bool
    {
        $this->assertActive();

        return array_key_exists($key, $this->data);
    }

    public function forget(string $key): void
    {
        $this->assertActive();

        unset($this->data[$key]);
    }

    public function regenerate(): bool
    {
        $this->assertActive();

        return true;
    }

    public function flash(string $key, array $messages): void
    {
        $this->assertActive();

        $this->data[self::FLASH_KEY][$key] = $messages;
    }

    public function getFlash(string $key): array
    {
        $this->assertActive();

        $messages = $this->data[self::FLASH_KEY][$key] ?? [];
        unset($this->data[self::FLASH_KEY][$key]);

        return $messages;
    }

    private function assertActive(): void
    {
        if (!$this->active) {
            throw new SessionException('The session has not been started.');
        }
    }
}
