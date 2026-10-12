<?php

declare(strict_types=1);

namespace EchoFusion\SessionManager\Tests;

use EchoFusion\Contracts\SessionManager\SessionInterface;
use EchoFusion\SessionManager\Exceptions\SessionException;
use EchoFusion\SessionManager\NativeSession;
use EchoFusion\SessionManager\SessionOptions;
use PHPUnit\Framework\TestCase;
use function sys_get_temp_dir;

/**
 * PHP sessions are process-global and cannot start once output was sent,
 * so every test runs in its own process.
 *
 * @runTestsInSeparateProcesses
 *
 * @preserveGlobalState disabled
 */
final class NativeSessionTest extends TestCase
{
    use SessionContractTrait;

    protected function startedSession(): SessionInterface
    {
        $session = new NativeSession($this->options());
        $session->start();

        return $session;
    }

    public function testLifecycleAndOptions(): void
    {
        $session = new NativeSession($this->options(name: 'echo_sid'));
        $this->assertFalse($session->isActive());

        $session->start();
        $this->assertTrue($session->isActive());
        $this->assertSame('echo_sid', session_name());

        $session->put('a', 1);
        $session->save();
        $this->assertFalse($session->isActive());
    }

    public function testDefaultOptions(): void
    {
        $options = new SessionOptions();

        $this->assertNull($options->name);
        $this->assertTrue($options->secure);
        $this->assertTrue($options->httpOnly);
        $this->assertSame('Lax', $options->sameSite);
        $this->assertSame('/', $options->path);
    }

    public function testStartTwiceThrows(): void
    {
        $session = $this->startedSession();

        $this->expectException(SessionException::class);
        $this->expectExceptionMessage('already been started');
        $session->start();
    }

    public function testStartAfterOutputThrows(): void
    {
        // PHPUnit buffers output; flush it so PHP considers the headers sent,
        // then restore the buffers so PHPUnit's own bookkeeping stays intact.
        $level = ob_get_level();
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        echo ' ';
        flush();
        for ($i = 0; $i < $level; ++$i) {
            ob_start();
        }

        $this->expectException(SessionException::class);
        $this->expectExceptionMessage('Headers have already been sent');
        (new NativeSession($this->options()))->start();
    }

    public function testOperationsRequireStartedSession(): void
    {
        $session = new NativeSession($this->options());

        foreach ([
            static fn () => $session->get('a'),
            static fn () => $session->put('a', 1),
            static fn () => $session->has('a'),
            static fn () => $session->forget('a'),
            static fn () => $session->regenerate(),
            static fn () => $session->flash('a', []),
            static fn () => $session->getFlash('a'),
        ] as $operation) {
            try {
                $operation();
                $this->fail('Expected SessionException');
            } catch (SessionException $e) {
                $this->assertStringContainsString('not been started', $e->getMessage());
            }
        }
    }

    private function options(?string $name = null): SessionOptions
    {
        return new SessionOptions(name: $name, secure: false, savePath: sys_get_temp_dir());
    }
}
