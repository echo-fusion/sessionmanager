<?php

declare(strict_types=1);

namespace EchoFusion\SessionManager\Middleware;

use EchoFusion\Contracts\SessionManager\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Starts the session (if it is not active yet) before the rest of the pipeline runs.
 */
final class StartSessionMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly SessionInterface $session)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->session->isActive()) {
            $this->session->start();
        }

        return $handler->handle($request);
    }
}
