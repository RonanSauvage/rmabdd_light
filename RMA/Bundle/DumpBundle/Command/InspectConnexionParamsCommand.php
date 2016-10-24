<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use RMA\Bundle\DumpBundle\ConnexionDB\ConnexionDB;

class InspectConnexionParamsCommand extends CommonCommand {
    
    protected function configure() {
      
        $this->setName('rma:dump:inspect')
            ->setDescription("Permet d'inspecter les paramètres définis pour les connexions DB")
            ->setAliases(['inspect']);       
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {          
        $io = new SymfonyStyle($input, $output);
        
        // On charge l'array params avec les options / parameters
        $params = $this->constructParamsArray($input);
        
        $io->title('Description des connexions à partir des paramètres définis  :');

        $headers = array (
            'Nom de la connexion', 'Host', 'Driver', 'Username', 'Password', 'Port', 'Excludes'
        );
        
        $rows = array();
        foreach ($params['connexions'] as $name => $params)
        {             
            $connexion = new ConnexionDB ($params);
            $connexion->setName($name);
            $connexion_array = array (
                $connexion->getName(),
                $connexion->getHost(),
                $connexion->getDriver(),
                $connexion->getUsername(),
                $connexion->getPassword(),
                $connexion->getPort()
            );
            array_push($rows, $connexion_array);
        }
        $io->table($headers, $rows);
    }    
}
