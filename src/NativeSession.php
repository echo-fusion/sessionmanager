<?php

declare(strict_types=1);

namespace EchoFusion\SessionManager;

use EchoFusion\Contracts\SessionManager\SessionInterface;
use EchoFusion\SessionManager\Exceptions\SessionException;
use const PHP_SESSION_ACTIVE;
use function array_key_exists;
use function headers_sent;
use function session_name;
use function session_regenerate_id;
use function session_save_path;
use function session_set_cookie_params;
use function session_start;
use function session_status;
use function session_write_close;
use function sprintf;

/**
 * Session backed by PHP's native session handling ($_SESSION).
 */
final class NativeSession implements SessionInterface
{
    private const FLASH_KEY = '_flash';

    public function __construct(private readonly SessionOptions $options = new SessionOptions())
    {
    }

    public function start(): void
    {
        if ($this->isActive()) {
            throw new SessionException('Session has already been started.');
        }

        if (headers_sent($file, $line)) {
            throw new SessionException(sprintf('Headers have already been sent by %s:%d.', $file, $line));
        }

        session_set_cookie_params([
            'lifetime' => $this->options->lifetime,
            'path' => $this->options->path,
            'domain' => $this->options->domain,
            'secure' => $this->options->secure,
            'httponly' => $this->options->httpOnly,
            'samesite' => $this->options->sameSite,
        ]);

        if ($this->options->name !== null) {
            session_name($this->options->name);
        }

        if ($this->options->savePath !== null) {
            /** @psalm-suppress UnusedFunctionCall */
            session_save_path($this->options->savePath);
        }

        if (!session_start()) {
            throw new SessionException('Unable to start the session.');
        }
    }

    public function save(): void
    {
        session_write_close();
    }

    public function isActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->assertActive();

        return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
    }

    public function put(string $key, mixed $value): void
    {
        $this->assertActive();

        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        $this->assertActive();

        return array_key_exists($key, $_SESSION);
    }

    public function forget(string $key): void
    {
        $this->assertActive();

        unset($_SESSION[$key]);
    }

    public function regenerate(): bool
    {
        $this->assertActive();

        return session_regenerate_id(true);
    }

    public function flash(string $key, array $messages): void
    {
        $this->assertActive();

        $_SESSION[self::FLASH_KEY][$key] = $messages;
    }

    public function getFlash(string $key): array
    {
        $this->assertActive();

        $messages = $_SESSION[self::FLASH_KEY][$key] ?? [];
        unset($_SESSION[self::FLASH_KEY][$key]);

        return $messages;
    }

    private function assertActive(): void
    {
        if (!$this->isActive()) {
            throw new SessionException('The session has not been started.');
        }
    }
}
