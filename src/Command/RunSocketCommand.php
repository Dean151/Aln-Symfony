<?php

declare(strict_types=1);

namespace App\Command;

use App\Queue\AsyncConsumer;
use App\Socket\AsyncServer;
use App\Socket\FeederCommunicator;
use Psr\Log\LoggerInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'aln:socket:run',
    description: 'Run the rabbitmq client & websocket server',
    hidden: false
)]
final class RunSocketCommand extends Command implements SignalableCommandInterface
{
    private ?LoopInterface $loop = null;

    public function __construct(
        private readonly AsyncConsumer $queueConsumer,
        private readonly AsyncServer $socketServer,
        private readonly FeederCommunicator $communicator,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('Run the rabbitmq client & websocket server');
        $this->setDescription('Run a bunny rabbitmq client ; and a websocket server to communicate with the feeders');
    }

    /**
     * @return-stan Command::*
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->logger->notice('Starting socket…');
            $this->loop = Loop::get();

            $this->queueConsumer->start($this->loop, $this->communicator);
            $this->socketServer->start($this->loop, $this->communicator);

            $this->loop->run();

            $this->logger->notice('Socket closed.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->logger->error('Socket errored: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * @return int[]
     */
    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSignal(int $signal, int|false $previousExitCode = 0): false|int
    {
        $this->logger->notice('Signal retrieved: '.$signal.', shutting down.');

        $this->queueConsumer->shutdown();
        $this->socketServer->shutdown();
        $this->loop?->stop();

        return false;
    }
}
