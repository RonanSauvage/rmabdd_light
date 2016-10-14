<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputOption;

use RMA\Bundle\DumpBundle\Factory\RDumpFactory;
use RMA\Bundle\DumpBundle\Tools\ExportDatabase;
use RMA\Bundle\DumpBundle\ConnexionDB\ConnexionDB;
use RMA\Bundle\DumpBundle\Tools\Tools;


class ExportCommand extends CommonCommand {

    protected function configure() {
      
        $this->setName('rma:dump:export')
            ->setDescription("Permet de réaliser un export d'une base de données.")
            ->addOption('script', null, InputOption::VALUE_OPTIONAL, "Le script a appliquer sur le fichier pour l'export")
            ->addOption('repertoire_name', null, InputOption::VALUE_OPTIONAL, "Permet de donner un nom custom à l'export")
            ->addOption('keep_tmp', null, InputOption::VALUE_NONE, "Permet de ne pas effacer la base de données temporaire créée")
            ->addOption('name_database_temp', null, InputOption::VALUE_OPTIONAL, "Permet de ne pas effacer la base de données temporaire créée")
            ->setAliases(['export']);       
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {       
        $io = new SymfonyStyle($input, $output);
        
        // On charge l'array params avec les options / parameters
        $params = $this->hydrateCommand($input, $io);
 
          // On charge l'objet dump pour gérer toutes les fonctionnalités 
        $dump = RDumpFactory::create($params);
        
        // Par défaut avec l'option all toutes les bases seront extraites
        $databases = $dump->rmaDumpGetListDatabases();
        
        // Si aucun nom n'a été spécifié, on génère un nom aléatoire
        if(!$params['name_database_temp']){
            $params['name_database_temp'] = uniqid();
        }
        
        $script = Tools::formatDirWithFile($params['dir_script_migration'], $params['script']);
        
        // On vérifie que le fichier de script est disponible
        if(!file_exists($script)){
            throw new \Exception ('Le fichier de script de migration est introuvable avec la configuration définie : ' . $script . ". Vous pouvez définir votre script d'export avec le parameter rma_script ou en option de la commande"); 
        }
        
        // On vérifie qu'il n'existe pas déjà une base de données avec ce nom et
        if(in_array($params['name_database_temp'], $databases)){
            throw new \Exception ('Il existe déjà une base de données avec le nom ' . $params['name_database_temp']);
        }
           
        $databases = array($io->choice('Sélectionnez la base de données à sauvegarder', $databases)); 

        DumpCommand::dumpDatabases($io, $databases, $dump, $output);

        $connexiondb = new ConnexionDB($params);
        $exportDatabase = new ExportDatabase($connexiondb, $params);
        $dir = $params['dir_dump'] . DIRECTORY_SEPARATOR .  $params['repertoire_name'] . DIRECTORY_SEPARATOR . $databases[0] .'.sql' ; 

        $exportDatabase->createDatabaseWithSqlFic($dir, $params['name_database_temp']);

        $exportDatabase->lauchScriptForMigration($script, $params['name_databasee_temp']);

        // On change le répertoire de destination pour mettre la base de données migrée dans export
        $params['dir_dump'] = $params['dir_export'];
        $dump = RDumpFactory::create($params);

        DumpCommand::dumpDatabases($io, array($params['name_database_temp']), $dump, $output);
        
        if($params['keep_tmp'] != 'yes'){
             $this->deleteDatabase($params['name_database_temp'], $params['keep_tmp'], $exportDatabase, $io);
        }
    }
    
    public function hydrateCommand(InputInterface $input, $io)
    {
        $params = $this->constructParamsArray($input);
        $params['name_database_temp'] = false;
        
        if ($input->getOption('repertoire_name'))
        {
            $name_rep =  Tools::cleanString($input->getOption('repertoire_name')) ;
            $params['repertoire_name'] = $name_rep . '__' . uniqid();
        }
  
        if ($input->getOption('name_database_temp'))
        {
            $params['name_database_temp'] = Tools::cleanString($input->getOption('name_database_temp'));
        }

        // Il s'agit ici simplement d'utiliser un dump temporaire donc on force à non les options de zip et ftp
        $params['zip'] = 'no';
        $params['ftp'] = 'no';
        $params['dir_dump'] = $params['dir_tmp'];
        return $params;
    }
    
    /**
     * Permet de delete la database définie
     * @param string $nameDatabase
     * @param string $keepTmp {yes | no}
     * @param ExportDatabase $exportDatabase
     * @param SymfonyStyle $io
     */
    public function deleteDatabase($nameDatabase, $keepTmp, ExportDatabase $exportDatabase, SymfonyStyle $io){
        if($keepTmp != "yes" ) {

           $exportDatabase->deleteDB($nameDatabase);
           $io->success('La base temporaire '. $nameDatabase . ' a été correctement effacée.');
       }  
       else {
           $io->success('La base temporaire '. $nameDatabase . ' a été correctement conservée.');
       }
    }
}
