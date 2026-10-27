<?php

declare(strict_types=1);

namespace App\Command;

use App\Socket\SocketHeartbeat;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'aln:socket:restart',
    description: 'Ask the running socket to exit gracefully, so its process manager restarts it',
    help: 'The socket must run under a process manager restarting it on exit (e.g. supervisor with autorestart=true).',
)]
final class RestartSocketCommand
{
    public function __construct(
        private readonly SocketHeartbeat $heartbeat,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option('Wait for a new socket process to be up and running')]
        bool $wait = false,
        #[Option('Maximum time to wait, in seconds')]
        int $timeout = 60,
    ): int {
        $requestedAt = $this->heartbeat->requestRestart();
        $io->success('Socket restart requested.');
        if (!$wait) {
            return Command::SUCCESS;
        }

        $deadline = $requestedAt + $timeout;
        while (!$this->heartbeat->hasRestartedSince($requestedAt)) {
            if ($this->heartbeat->now() >= $deadline) {
                $io->error("The socket did not restart within {$timeout} seconds.");

                return Command::FAILURE;
            }
            $this->clock->sleep(SocketHeartbeat::RESTART_CHECK_INTERVAL);
        }
        $io->success('Socket restarted.');

        return Command::SUCCESS;
    }
}
