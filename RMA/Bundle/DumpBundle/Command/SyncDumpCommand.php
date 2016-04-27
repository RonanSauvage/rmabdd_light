<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

use RMA\Bundle\DumpBundle\Factory\RToolsFactory; 
use RMA\Bundle\DumpBundle\Command\CommonCommand;

class SyncDumpCommand extends CommonCommand {
    
    protected function configure() {
      
        $this->setName('rma:dump:sync')
            ->setDescription('Permet de synchroniser la sauvegarde des dumps')
            ->addOption('dir_dump', '', InputOption::VALUE_OPTIONAL, 'Permet de définir le répertoire à synchroniser. Si pas spécifié, on prend le répertoire dans parameters');      
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {                
        $params = $this->constructParamsArray($input);
        
        $io = new SymfonyStyle($input, $output);
    
        if(($input->getOption('dir_dump')))
        {
            $params['dir_dump'] = $input->getOption('dir_dump');
        }
        
        $this->syncCommand($io, $params);
    }
    
    /**
     * Permet de synchroniser le fichier 
     * @param SymfonyStyle $io
     * @param array $params
     */
    public static function syncCommand($io, Array $params)
    {
        $io->title('Synchronisation du répertoire : ' . $params['dir_dump']);

        $tools = RToolsFactory::create($params);
        $infos = $tools->rmaSyncRep();
      
        $io->note('Nombre de dumps référencés dans les logs : ' . $infos['count_initial']);
        $io->note('Nombre de dumps trouvés dans le répertoire : ' . $infos['count_final']);
        $io->note('Nombre de dumps effacés des logs : ' . $infos['synchro']);
      
        $io->success('Synchronisation du répertoire finie');
    }
}
