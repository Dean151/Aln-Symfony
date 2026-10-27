<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Command\RestartSocketCommand;
use App\Socket\SocketHeartbeat;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Uses an in-memory pool: the shared one would make a running socket exit.
 */
final class SocketHeartbeatTest extends TestCase
{
    private MockClock $clock;
    private SocketHeartbeat $heartbeat;

    protected function setUp(): void
    {
        $this->clock = new MockClock();
        $this->heartbeat = new SocketHeartbeat(new ArrayAdapter(), $this->clock);
    }

    public function testAliveUntilHeartbeatIsStale(): void
    {
        $this->assertFalse($this->heartbeat->isAlive());

        $this->heartbeat->beat($this->heartbeat->now());
        $this->assertTrue($this->heartbeat->isAlive());

        $this->clock->sleep(SocketHeartbeat::MAX_AGE + 1);
        $this->assertFalse($this->heartbeat->isAlive());
    }

    public function testRestartRequestOnlyTargetsOlderProcesses(): void
    {
        $oldProcessStartedAt = $this->heartbeat->now();
        $this->assertFalse($this->heartbeat->isRestartRequested($oldProcessStartedAt));

        $this->clock->sleep(0.001);
        $requestedAt = $this->heartbeat->requestRestart();
        $this->assertTrue($this->heartbeat->isRestartRequested($oldProcessStartedAt));

        $this->clock->sleep(0.001);
        $this->assertFalse($this->heartbeat->isRestartRequested($this->heartbeat->now()));
        $this->assertGreaterThan($oldProcessStartedAt, $requestedAt);
    }

    public function testHasRestartedSince(): void
    {
        $this->heartbeat->beat($this->heartbeat->now());
        $this->clock->sleep(0.001);
        $requestedAt = $this->heartbeat->requestRestart();

        // The old process keeps beating until it notices the request
        $this->heartbeat->beat($requestedAt - 1);
        $this->assertFalse($this->heartbeat->hasRestartedSince($requestedAt));

        $this->clock->sleep(0.001);
        $this->heartbeat->beat($this->heartbeat->now());
        $this->assertTrue($this->heartbeat->hasRestartedSince($requestedAt));
    }

    public function testRestartCommandWithoutWait(): void
    {
        $startedAt = $this->heartbeat->now();
        $this->clock->sleep(0.001);

        $tester = new CommandTester($this->createCommand());
        $tester->execute([]);

        $tester->assertCommandIsSuccessful();
        $this->assertTrue($this->heartbeat->isRestartRequested($startedAt));
    }

    public function testRestartCommandWaitTimesOutWhenSocketDoesNotComeBack(): void
    {
        $tester = new CommandTester($this->createCommand());
        $tester->execute(['--wait' => true, '--timeout' => 5]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('did not restart within 5 seconds', $tester->getDisplay());
    }

    public function testRestartCommandWaitSucceedsOnceNewProcessBeats(): void
    {
        // Simulates supervisor starting a new process while the command waits
        $clock = new class($this->clock, $this->heartbeat) implements ClockInterface {
            public function __construct(private readonly MockClock $inner, private readonly SocketHeartbeat $heartbeat)
            {
            }

            public function now(): \DateTimeImmutable
            {
                return $this->inner->now();
            }

            public function sleep(float|int $seconds): void
            {
                $this->inner->sleep($seconds);
                $this->heartbeat->beat($this->heartbeat->now());
            }

            public function withTimeZone(\DateTimeZone|string $timezone): static
            {
                return $this;
            }
        };

        $tester = new CommandTester(new RestartSocketCommand($this->heartbeat, $clock));
        $tester->execute(['--wait' => true]);

        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Socket restarted.', $tester->getDisplay());
    }

    private function createCommand(): RestartSocketCommand
    {
        return new RestartSocketCommand($this->heartbeat, $this->clock);
    }
}
