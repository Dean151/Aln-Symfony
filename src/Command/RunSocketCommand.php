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
use Symfony\Component\HttpKernel\KernelInterface;

use function Safe\file_get_contents;
use function Safe\file_put_contents;
use function Safe\getmypid;
use function Safe\unlink;

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
        private readonly KernelInterface $kernel,
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
        $filename = $this->kernel->getProjectDir().'/var/socket.pid';
        try {
            if (file_exists($filename)) {
                $this->logger->error('Socket is already running with pid '.file_get_contents($filename));

                return Command::FAILURE;
            }
            file_put_contents($filename, getmypid());

            $this->loop = Loop::get();

            $this->queueConsumer->start($this->loop, $this->communicator);
            $this->socketServer->start($this->loop, $this->communicator);

            $this->loop->run();

            unlink($filename);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            unlink($filename);

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
        $this->queueConsumer->shutdown();
        $this->socketServer->shutdown();
        $this->loop?->stop();

        return false;
    }
}
