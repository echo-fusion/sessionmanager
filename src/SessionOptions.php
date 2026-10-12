<?php

declare(strict_types=1);

namespace EchoFusion\SessionManager;

/**
 * Cookie and storage options for {@see NativeSession}.
 *
 * Defaults are secure: HTTPS-only, HttpOnly and SameSite=Lax cookie.
 */
final class SessionOptions
{
    /**
     * @param non-empty-string|null $name session cookie name, null keeps the PHP default
     * @param int $lifetime cookie lifetime in seconds, 0 means until the browser closes
     * @param 'Lax'|'Strict'|'None' $sameSite
     * @param string|null $savePath directory for session files, null keeps the PHP default
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly int $lifetime = 0,
        public readonly string $path = '/',
        public readonly string $domain = '',
        public readonly bool $secure = true,
        public readonly bool $httpOnly = true,
        public readonly string $sameSite = 'Lax',
        public readonly ?string $savePath = null,
    ) {
    }
}
