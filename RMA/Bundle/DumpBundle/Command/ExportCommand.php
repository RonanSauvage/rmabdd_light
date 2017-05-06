<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputOption;

use RMA\Bundle\DumpBundle\Factory\RDumpFactory;
use RMA\Bundle\DumpBundle\ConnexionDB\ConnexionDB;
use RMA\Bundle\DumpBundle\Tools\Tools;
use RMA\Bundle\DumpBundle\Interfaces\ConnexionDBInterface;

class ExportCommand extends CommonCommand {

    protected function configure() {
      
        $this->setName('rma:dump:export')
            ->setDescription("Permet de réaliser un export d'une base de données.")
            ->addOption('script', null, InputOption::VALUE_REQUIRED, "Le script a appliquer sur le fichier pour l'export")
            ->addOption('repertoire_name', null, InputOption::VALUE_REQUIRED, "Permet de donner un nom custom à l'export")
            ->addOption('keep_tmp', null, InputOption::VALUE_NONE, "Permet de ne pas effacer la base de données temporaire créée")
            ->addOption('name_database_temp', null, InputOption::VALUE_REQUIRED, "Permet de ne pas effacer la base de données temporaire créée")
            ->addOption('ftp', null, InputOption::VALUE_NONE, "Permet d'envoyer l'export en FTP selon les parmaètres définis")
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

        // On vérifie qu'il n'existe pas déjà une base de données avec ce nom
        if(in_array($params['name_database_temp'], $databases)){
           throw new \Exception ('Il existe déjà une base de données avec le nom ' . $params['name_database_temp']);
        }
           
        $databases = array($io->choice('Sélectionnez la base de données à sauvegarder', $databases)); 

        DumpCommand::dumpDatabases($io, $databases, $dump, $output);

        $connexiondb = new ConnexionDB($params);
        
        $rmaExportManager = $this->getContainer()->get('rma.export.manager');        
        
        $dir = $params['dir_dump'] . DIRECTORY_SEPARATOR .  $params['repertoire_name'] . DIRECTORY_SEPARATOR . $databases[0] .'.sql' ; 

        $io->title('Création de la base de données temporaire : ');
        try {
            $rmaExportManager->createDatabaseWithSqlFic($dir, $params['name_database_temp'], $connexiondb);
            $io->success('Base de données ' . $params['name_database_temp'] .' correctement créée.');
        }
        catch (\Exception $e){
            $params['logger']->error('Erreur lors de la création de la base de données . ' . $e->getMessage());
            throw new \Exception('Erreur lors de la création de la base de données. ' . $e->getMessage());
        }
      
        $io->title('Lancement du script : ' . $script);
        try {
            $rmaExportManager->lauchScriptForMigration($script, $params['name_database_temp'], $connexiondb);
            $io->success("Le script s'est correctement executé.");
        }
        catch (\Exception $e){
            $params['logger']->error('Erreur lors du script de migration pour export. ' . $e->getMessage());
            $this->deleteDatabase($params['name_database_temp'], 'no', $connexiondb, $io);
            throw new \Exception('Erreur lors du lancement du script de migration. ' . $e->getMessage());
        }
    
        // On change le répertoire de destination pour mettre la base de données migrée dans export
        $params['dir_dump'] = $params['dir_export'];
        $dump = RDumpFactory::create($params);

        DumpCommand::dumpDatabases($io, array($params['name_database_temp']), $dump, $output);
        
        $this->deleteDatabase($params['name_database_temp'], $params['keep_tmp'], $connexiondb, $io);

        FtpCommand::saveDumpInFtp($io, $dump, $params);
    }
    
    public function hydrateCommand(InputInterface $input, SymfonyStyle $io)
    {   
        $response = $this->loadOptionsAndParameters($input);
        $params = $response['params'];
        $params['name_database_temp'] = false;
        
        // Il s'agit ici simplement d'utiliser un dump temporaire donc on force à non les options de zip et ftp
        $params['zip'] = 'no';
        $params['dir_dump'] = $params['dir_tmp'];
        $params['dir_fic'] = $params['dir_dump'];
  
        if ($input->getOption('name_database_temp'))
        {
            $params['name_database_temp'] = Tools::cleanString($input->getOption('name_database_temp'));
        }

        $params = $this->selectOne($params['connexions'], $response['fields_connexion'], $io, $response['name_connexion'], $params);

        // On surcharge le paramètre rma_ftp défini pour les dump selon que l'option était été envoyée ou non
        if ($input->getOption('ftp'))
        {
            $params['ftp'] = 'yes';
            $params['extension'] = '.sql';
            $params = $this->selectOne($params['ftps'], $response['fields_ftp'], $io, $response['name_ftp'], $params);
        }
        else {
            $params['ftp'] = 'no';
        }

        return $params;
    }
    
    /**
     * Permet de delete la database définie
     * @param string $nameDatabase
     * @param string $keepTmp {yes | no}
     * @param ConnexionDBInterface $connexionDB
     * @param SymfonyStyle $io
     */
    public function deleteDatabase($nameDatabase, $keepTmp, ConnexionDBInterface $connexionDB, SymfonyStyle $io){
        $io->title('Gestion de la base de données temporaire : ');
        if($keepTmp != "yes" ) {
           $rmaDatabaseManager = $this->getContainer()->get('rma.database.manager');
           $rmaDatabaseManager->deleteOneDatabase($connexionDB, $nameDatabase);
           $io->success('La base temporaire '. $nameDatabase . ' a été correctement effacée.');
       }  
       else {
           $io->success('La base temporaire '. $nameDatabase . ' a été correctement conservée.');
       }
    }
}
