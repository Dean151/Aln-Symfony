<?php

namespace App\Aln\Command;

use App\Aln\Socket\FeederCommunicator;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'aln:socket:run',
    description: '',
    hidden: false
)]
final class RunSocketCommand extends Command
{
    private FeederCommunicator $communicator;
    protected static $defaultName = 'aln:socket:run';

    public function __construct(FeederCommunicator $communicator)
    {
        $this->communicator = $communicator;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('Run the websocket server');
        $this->addArgument('port', InputArgument::OPTIONAL, 'The port to run the websocket server on', 9999);
    }

    /**
     * @return-stan Command::*
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $port = $input->hasArgument('port') ? $input->getArgument('port') : 9999;
        $output->writeln("Starting server on port {$port}");

        $app = IoServer::factory(new HttpServer(new WsServer($this->communicator)));
        $output->writeln('Server is running');
        $app->run();

        return Command::SUCCESS;
    }
}
