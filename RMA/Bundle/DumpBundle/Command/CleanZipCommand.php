<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

use RMA\Bundle\DumpBundle\Factory\RToolsFactory; 

class CleanZipCommand extends CommonCommand {
    
    protected function configure() {
      
        $this->setName('rma:zip:clean')
            ->setDescription('Permet de nettoyer les zips')
            ->addOption('nb_jour', '', InputOption::VALUE_REQUIRED, 'Permet de supprimer tous les zip plus anciens que le nombre de jours défini')
            ->addOption('dir_zip', '', InputOption::VALUE_REQUIRED, 'Permet de définir le répertoire a clean. Si pas spécifié, on prend le répertoire dans parameters')
            ->addOption('nombre_zip', '', InputOption::VALUE_REQUIRED, 'Permet de définir le nombre de zip à conserver');      
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {            
        $params = $this->constructParamsArray($input);
        $io = new SymfonyStyle($input, $output);
 
        $this->cleanCommandZip($io, $params);
    }
    
    /**
     * Permet de lancer un clean du répertoire zip
     * @param SymfonyStyle $io
     * @param Array $params
     */
    public static function cleanCommandZip($io, $params)
    {
        $tools = RToolsFactory::create($params);
     
        // On clean si paramétré les dumps selon une date de conservation
        
        // On clean si paramétré les dumps selon un nombre maximum à en garder
        $io->title('Conservation des ' . $params['nombre_dump'] .' derniers zips');
        $response_clean_nombre = $tools->rmaDeleteOldZip();
        $io->success($response_clean_nombre);
    }
}
