<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use RMA\Bundle\DumpBundle\Factory\RToolsFactory; 

class CleanDumpCommand extends ContainerAwareCommand {
    
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
        $dir_dump = $this->getContainer()->getParameter('rma_dir_dump');
        $nb_jour = $this->getContainer()->getParameter('rma_nb_jour');
    
        if(($input->getOption('dir_dump')))
        {
            $dir_dump = $input->getOption('dir_dump');
        }
        
        if(($input->getOption('nb_jour')))
        {
            $number_clean = $input->getOption('nb_jour');
            $this->cleanCommand($input, $output, $dir_dump, $number_clean);
        }
    }
    
    public static function cleanCommand(InputInterface $input, OutputInterface $output, $dir_dump, $number_clean)
    {
        $tools = RToolsFactory::create();
        $output->writeln('Suppression des dumps de plus de ' . $number_clean . ' jours');
        $response_clean = $tools->rmaDeleteOldDump($dir_dump, $number_clean);
        $output->writeln($response_clean);
    }
}
