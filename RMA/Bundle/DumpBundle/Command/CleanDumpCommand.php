<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

use RMA\Bundle\DumpBundle\Factory\RToolsFactory; 

class CleanDumpCommand extends CommonCommand {
    
    protected function configure() {
      
        $this->setName('rma:dump:clean')
            ->setDescription('Permet de nettoyer les dumps')
            ->addOption('nb_jour', '', InputOption::VALUE_REQUIRED, 'Permet de supprimer tous les dump plus anciens que le nombre de jours défini')
            ->addOption('dir_dump', '', InputOption::VALUE_REQUIRED, 'Permet de définir le répertoire a clean. Si pas spécifié, on prend le répertoire dans parameters')
            ->addOption('nombre_dump', '', InputOption::VALUE_REQUIRED, 'Permet de définir le nombre de dump à conserver');      
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {            
        $params = $this->constructParamsArray($input);
        $io = new SymfonyStyle($input, $output);
 
        SyncDumpCommand::syncCommand($io, $params);
        $this->cleanCommand($io, $params);
    }
    
    /**
     * Permet de lancer un clean 
     * @param SymfonyStyle $io
     * @param Array $params
     */
    public static function cleanCommand(SymfonyStyle $io, $params)
    {
        $tools = RToolsFactory::create($params);
        
         // On clean si paramétré les dumps selon une date de conservation
        $io->title('Clean des anciens dumps de plus de ' . $params['nb_jour'] .' jours');
        
        if($tools->rmaTryToAccessDumpIni()){
            
            $response_clean = $tools->rmaDeleteOldDump();
            $io->success($response_clean);

            // On clean si paramétré les dumps selon un nombre maximum à en garder
            $io->title('Conservation des ' . $params['nombre_dump'] .' derniers dumps');
            $response_clean_nombre = $tools->rmaDeleteDumpAfterThan();
            $io->success($response_clean_nombre);
        }
        else {
            $io->success("Le répertoire n'a pas besoin d'être clean");
        }     
    }
}
