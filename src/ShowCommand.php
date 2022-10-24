<?php namespace Acme;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\Console\Helper\Table;

class ShowCommand extends Command {

    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;

        parent::__construct();
    }
    
    public function configure()
    {
        $this->setName('show')
             ->setDescription('Show a movie')
             ->addArgument('name', InputArgument::REQUIRED, 'A movie name')
             ->addOption('fullPlot', null, InputOption::VALUE_NONE, 'Show movie plot');
    }

    public function execute(InputInterface $input, OutputInterface $output){
        $this->showMovie($input, $output);
        return Command::SUCCESS;
    }

    public function showMovie(InputInterface $input, OutputInterface $output){
        $endpoint = 'https://omdbapi.com/?apikey=7bcaaa1&t=' . $input->getArgument('name');
        if($input->getOption('fullPlot') !== false) {
            $endpoint = $endpoint . '&plot=full';
        }
        $response = $this->client->get($endpoint)->getBody()->getContents();
        $table = new Table($output);
        foreach(json_decode($response) as $key => $value){
            if($key != 'Ratings'){
                $item = [$key, $value];
                $table->addRow($item);
            }
        }
        $table->setColumnMaxWidth(1, 100);
        $table->render();
    }
}