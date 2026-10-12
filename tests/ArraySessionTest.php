<?php

declare(strict_types=1);

namespace EchoFusion\SessionManager\Tests;

use EchoFusion\Contracts\SessionManager\SessionInterface;
use EchoFusion\SessionManager\ArraySession;
use EchoFusion\SessionManager\Exceptions\SessionException;
use PHPUnit\Framework\TestCase;

final class ArraySessionTest extends TestCase
{
    use SessionContractTrait;

    protected function startedSession(): SessionInterface
    {
        $session = new ArraySession();
        $session->start();

        return $session;
    }

    public function testLifecycle(): void
    {
        $session = new ArraySession();
        $this->assertFalse($session->isActive());

        $session->start();
        $this->assertTrue($session->isActive());

        $session->save();
        $this->assertFalse($session->isActive());
    }

    public function testStartTwiceThrows(): void
    {
        $session = $this->startedSession();

        $this->expectException(SessionException::class);
        $session->start();
    }

    /**
     * @dataProvider operations
     */
    public function testOperationsRequireStartedSession(callable $operation): void
    {
        $this->expectException(SessionException::class);
        $this->expectExceptionMessage('not been started');

        $operation(new ArraySession());
    }

    /**
     * @return array<string, array{callable}>
     */
    public function operations(): array
    {
        return [
            'get' => [static fn (SessionInterface $s) => $s->get('a')],
            'put' => [static fn (SessionInterface $s) => $s->put('a', 1)],
            'has' => [static fn (SessionInterface $s) => $s->has('a')],
            'forget' => [static fn (SessionInterface $s) => $s->forget('a')],
            'regenerate' => [static fn (SessionInterface $s) => $s->regenerate()],
            'flash' => [static fn (SessionInterface $s) => $s->flash('a', [])],
            'getFlash' => [static fn (SessionInterface $s) => $s->getFlash('a')],
        ];
    }
}
