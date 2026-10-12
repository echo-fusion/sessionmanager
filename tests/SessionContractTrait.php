<?php

declare(strict_types=1);

namespace EchoFusion\SessionManager\Tests;

use EchoFusion\Contracts\SessionManager\SessionInterface;

/**
 * Behaviour every started SessionInterface implementation must have.
 */
trait SessionContractTrait
{
    abstract protected function startedSession(): SessionInterface;

    public function testPutGetHasForget(): void
    {
        $session = $this->startedSession();

        $this->assertFalse($session->has('user'));
        $this->assertSame('none', $session->get('user', 'none'));

        $session->put('user', 7);
        $this->assertTrue($session->has('user'));
        $this->assertSame(7, $session->get('user'));

        $session->forget('user');
        $this->assertFalse($session->has('user'));
        $this->assertNull($session->get('user'));
    }

    public function testHasIsTrueForNullValue(): void
    {
        $session = $this->startedSession();
        $session->put('nullable', null);

        $this->assertTrue($session->has('nullable'));
        $this->assertSame('x', $session->get('nullable', 'x') ?? 'x');
    }

    public function testFlashIsReadOnce(): void
    {
        $session = $this->startedSession();

        $session->flash('errors', ['Email is required']);

        $this->assertSame(['Email is required'], $session->getFlash('errors'));
        $this->assertSame([], $session->getFlash('errors'));
    }

    public function testFlashDoesNotCollideWithRegularKeys(): void
    {
        $session = $this->startedSession();
        $session->put('flash', 'mine');
        $session->flash('flash', ['a']);

        $this->assertSame('mine', $session->get('flash'));
        $this->assertSame(['a'], $session->getFlash('flash'));
    }

    public function testRegenerate(): void
    {
        $session = $this->startedSession();
        $session->put('a', 1);

        $this->assertTrue($session->regenerate());
        $this->assertSame(1, $session->get('a'));
    }
}
