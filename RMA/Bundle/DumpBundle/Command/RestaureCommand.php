<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use RMA\Bundle\DumpBundle\Command\CommonCommand;
use RMA\Bundle\DumpBundle\Tools\Tools;
use RMA\Bundle\DumpBundle\Factory\RDumpFactory;
use RMA\Bundle\DumpBundle\ConnexionDB\ConnexionDB;

class RestaureCommand extends CommonCommand {
    
    protected function configure() {
      
        $this->setName('rma:restaure:database')
            ->setDescription('Permet de restaurer une base de données.')
            ->addOption('new_database_name', null, InputOption::VALUE_REQUIRED, 'Permet de spécifier un nom pour la base de données')
            ->addOption('script_sql', null, InputOption::VALUE_REQUIRED, "Désigne le path d'accès au script SQL à restaurer")
            ->setAliases(['restaure']);       
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {   
        $io = new SymfonyStyle($input, $output);
        
        // On charge l'array params avec les options / parameters
        $params = $this->hydrateCommand($input, $io); 
        
        $rmaRestaureManager = $this->getContainer()->get('rma.restaure.manager');
        
        // On charge l'objet dump pour gérer toutes les fonctionnalités 
        $dump = RDumpFactory::create($params);
        
        $databases = $dump->rmaDumpGetListDatabases();
        
        $script = Tools::formatDirWithFile($params['dir_script_migration'], $params['script_sql']);
        
        // On vérifie qu'il n'existe pas déjà une base de données avec ce nom
        if(in_array($params['new_database_name'], $databases)){
           throw new \Exception ('Il existe déjà une base de données avec le nom ' . $params['new_database_name']);
        }
        
         // On vérifie que le fichier de script est disponible
        if(!file_exists($script)){
            throw new \Exception ('Le fichier de restauration est introuvable : ' . $script); 
        }
        
        $connexionDB = new ConnexionDB($params);
 
        $io->title('Lancement de la restauration de la base. Le nom de la base sera : ' . $params['new_database_name']);
        $rmaRestaureManager->restaureOneDatabase($connexionDB, $params['new_database_name'], $script);
        $io->success("La base de données a été correctement crée : " . $params['new_database_name']);    
    }
    
    public function hydrateCommand(InputInterface $input, SymfonyStyle $io)
    {   
        $response = $this->loadOptionsAndParameters($input);
        $params = $response['params'];
        $params['database_name'] = false;
        
        // Il s'agit ici simplement d'utiliser un dump temporaire donc on force à non les options de zip et ftp
        $params['zip'] = 'no';
        $params['dir_dump'] = $params['dir_tmp'];
        $params['dir_fic'] = $params['dir_dump'];
  
        if ($input->getOption('new_database_name'))
        {
            $params['new_database_name'] = Tools::cleanString($input->getOption('new_database_name'));
        }
        
        if ($input->getOption('script_sql'))
        {
            $params['script_sql'] = $input->getOption('script_sql');
        }

        $params = $this->selectOne($params['connexions'], $response['fields_connexion'], $io, $response['name_connexion'], $params);

        return $params;
    }
}
