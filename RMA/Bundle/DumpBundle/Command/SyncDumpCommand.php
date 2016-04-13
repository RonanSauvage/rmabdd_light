<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use RMA\Bundle\DumpBundle\Factory\RToolsFactory; 
use RMA\Bundle\DumpBundle\Command\CommonCommand;

class SyncDumpCommand extends CommonCommand {
    
    protected function configure() {
      
        $this->setName('rma:dump:sync')
            ->setDescription('Permet de synchroniser la sauvegarde des dumps')
            ->addOption(
               'dir_dump',
               '',
               InputOption::VALUE_OPTIONAL,
               'Permet de définir quel répertoire est à synchroniser. Si pas spécifié, on prend le répertoire dans parameters'
            );      
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {                
        $params = $this->constructParamsArray($input);
        
        $io = new SymfonyStyle($input, $output);
    
        if(($input->getOption('dir_dump')))
        {
            $params['dir_dump'] = $input->getOption('dir_dump');
        }
        
        $this->SyncCommand($io, $params['dir_dump'], $params['logger']);
    }
    
    public static function SyncCommand($io, $dir_dump, LoggerInterface $logger)
    {
        $tools = RToolsFactory::create(array('logger' => $logger));
        $infos = $tools->rmaSyncRep($dir_dump);
        $io->title('Synchronisation du répertoire : ' . $dir_dump);

        $io->note('Nombre de dumps référencés dans les logs : ' . $infos['count_initial']);
        $io->note('Nombre de dumps trouvés dans le répertoire : ' . $infos['count_final']);
        $io->note('Nombre de dumps effacés des logs : ' . $infos['synchro']);
      
        $io->success('Synchronisation du répertoire finie');
    }
}
