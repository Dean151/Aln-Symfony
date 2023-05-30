<?php

declare(strict_types=1);

namespace App\Command;

use App\Factory\AlnFeederFactory;
use App\Socket\FeederSimulator;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'aln:feeder:simulate',
    description: 'Run a fake feeder to connect on the websocket',
    hidden: false
)]
class SimulateFeederCommand extends Command implements SignalableCommandInterface
{
    private ?LoopInterface $loop = null;

    public function __construct(
        private readonly FeederSimulator $simulator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('Run a fake feeder to connect on the websocket');
        $this->setDescription('Run a fake feeder that behave just like a real feeder, and that will help to debug APIs.');
        $this->addArgument('identifier', InputArgument::OPTIONAL, 'The feeder identifier to simulate', AlnFeederFactory::AVAILABLE_FEEDER_IDENTIFIER);
        $this->addOption('not-responding', null, InputOption::VALUE_NONE, 'Simulate when feeder won\'t send responses');
        $this->addOption('fast', null, InputOption::VALUE_NONE, 'Accelerate response time for test');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $identifier = $input->getArgument('identifier');
        $unresponsiveFeeder = $input->getOption('not-responding') ? FeederSimulator::OPTION_UNRESPONSIVE : FeederSimulator::OPTION_NONE;
        $fastResponse = $input->getOption('fast') ? FeederSimulator::OPTION_FAST_RESPONSE : FeederSimulator::OPTION_NONE;
        $options = $unresponsiveFeeder | $fastResponse;

        $this->loop = Loop::get();

        $this->simulator->start($this->loop, $identifier, $options);
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

    public function handleSignal(int $signal): false|int
    {
        $this->simulator->shutdown();
        $this->loop?->stop();

        return false;
    }
}
