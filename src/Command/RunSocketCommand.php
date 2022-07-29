<?php

declare(strict_types=1);

namespace App\Command;

use App\Queue\AsyncConsumer;
use App\Socket\AsyncServer;
use App\Socket\FeederCommunicator;
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
    protected static $defaultName = 'aln:socket:run';

    private AsyncConsumer $queueConsumer;
    private AsyncServer $socketServer;
    private FeederCommunicator $communicator;

    private ?LoopInterface $loop = null;

    public function __construct(AsyncConsumer $queueConsumer, AsyncServer $socketServer, FeederCommunicator $communicator)
    {
        $this->queueConsumer = $queueConsumer;
        $this->socketServer = $socketServer;
        $this->communicator = $communicator;
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
        $this->loop = Loop::get();

        $this->queueConsumer->start($this->loop, $this->communicator);
        $this->socketServer->start($this->loop, $this->communicator);

        $this->loop->run();

        return Command::SUCCESS;
    }

    /**
     * @return int[]
     */
    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSignal(int $signal): void
    {
        $this->loop?->stop();
        $this->queueConsumer->shutdown();
        $this->socketServer->shutdown();
    }
}
