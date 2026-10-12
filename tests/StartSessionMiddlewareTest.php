<?php

declare(strict_types=1);

namespace EchoFusion\SessionManager\Tests;

use EchoFusion\SessionManager\ArraySession;
use EchoFusion\SessionManager\Middleware\StartSessionMiddleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class StartSessionMiddlewareTest extends TestCase
{
    public function testStartsSessionBeforeHandlingRequest(): void
    {
        $session = new ArraySession();
        $handler = new class($session) implements RequestHandlerInterface {
            public ?bool $activeDuringHandle = null;

            public function __construct(private ArraySession $session)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->activeDuringHandle = $this->session->isActive();

                return new Response(204);
            }
        };

        $response = (new StartSessionMiddleware($session))->process(new ServerRequest('GET', '/'), $handler);

        $this->assertTrue($handler->activeDuringHandle);
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testDoesNotStartAnAlreadyActiveSession(): void
    {
        $session = new ArraySession();
        $session->start();
        $handler = new class() implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $response = (new StartSessionMiddleware($session))->process(new ServerRequest('GET', '/'), $handler);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($session->isActive());
    }
}
