<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use RMA\Bundle\DumpBundle\Factory\RToolsFactory; 

class CleanDumpCommand extends CommonCommand {
    
    protected function configure() {
      
        $this->setName('rma:dump:clean')
            ->setDescription('Permet de nettoyer les dumps')
            ->addOption(
               'nb_jour',
               '',
               InputOption::VALUE_REQUIRED,
               'Permet de supprimer tous les dump plus ancien que le nombre de jours défini'
            )
            ->addOption(
               'dir_dump',
               '',
               InputOption::VALUE_OPTIONAL,
               'Permet de définir quel répertoire est à dump. Si pas spécifié, on prend le répertoire dans parameters'
            );      
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {            
        $params = $this->hydrateCommand($input);
        $io = new SymfonyStyle($input, $output);
   
        if(($input->getOption('dir_dump')))
        {
            $params['dir_dump'] = $input->getOption('dir_dump');
        }
        
        if(($input->getOption('nb_jour')))
        {
            $params['nb_jour'] = $input->getOption('nb_jour');

        }
        
        SyncDumpCommand::syncCommand($io, $params);
        $this->cleanCommand($io, $params);
    }
    
    public static function cleanCommand($io, $params)
    {
        $tools = RToolsFactory::create($params);
     
        $io->title('Clean des anciens dumps de plus de ' . $params['nb_jour'] .' jours');
        $response_clean = $tools->rmaDeleteOldDump($params['dir_dump'], $params['nb_jour']);
        $io->success($response_clean);
    }
    
    public function hydrateCommand(InputInterface $input)
    {
        $params = $this->constructParamsArray($input);
        return $params;
    }
}
