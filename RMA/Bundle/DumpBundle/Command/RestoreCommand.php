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
use RMA\Bundle\DumpBundle\Tools\SyncDump;

class RestaureCommand extends CommonCommand {
    
    protected function configure() {
      
        $this->setName('rma:restore:database')
            ->setDescription('Permet de restaurer une base de données.')
            ->addOption('new_database_name', null, InputOption::VALUE_REQUIRED, 'Permet de spécifier un nom pour la base de données')
            ->addOption('script_sql', null, InputOption::VALUE_REQUIRED, "Désigne le path d'accès au script SQL à restaurer")
            ->addOption('replace', null, InputOption::VALUE_NONE, "Permet de définir que nous allons remplacer une base existante")
            ->setAliases(['restore']);       
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {   
        $io = new SymfonyStyle($input, $output);

        // On charge l'array params avec les options / parameters
        $params = $this->hydrateCommand($input, $io); 
        $rmaRestaureManager = $this->getContainer()->get('rma.restaure.manager');
        $databaseManager = $this->getContainer()->get('rma.database.manager');
        
        // On charge l'objet dump pour gérer toutes les fonctionnalités 
        $dump = RDumpFactory::create($params);
        $connexionDB = new ConnexionDB($params);
        
        $databases = $dump->rmaDumpGetListDatabases();
        $replace = false;

        // Si l'utilisateur a spécifié l'option replace alors on ne gère pas d'unicité de nouveau nom de base
        if ($input->getOption('replace'))
        {
            // On garde l'information de procéder à sa suppression une fois le load des params fini
            $replace = true ;
        }
        // On vérifie qu'il n'existe pas déjà une base de données avec ce nom
        else if(in_array(strtolower($params['new_database_name']), $databases)){
           throw new \Exception ('Il existe déjà une base de données avec le nom ' . $params['new_database_name']);
        }
        
        $params = $this->loadScript($input, $io, $params); 
        
        // On vérifie que le fichier de script est disponible
        if(!file_exists($params['script_sql'])){
            throw new \Exception ('Le fichier de restauration est introuvable : ' . $params['script_sql']); 
        }   
        
        // On attend la dernière exécution pour supprimer la base de données si nécessaire
        if($replace){
            $databaseManager->deleteOneDatabase($connexionDB, $params['new_database_name']);
        }
 
        $io->title('Lancement de la restauration de la base. Le nom de la base sera : ' . $params['new_database_name']);
        $rmaRestaureManager->restaureOneDatabase($connexionDB, $params['new_database_name'], $params['script_sql']);
        $io->success("La base de données a été correctement restaurée : " . $params['new_database_name']);    
    }
    
    /**
     * Permet de changer l'array $params
     * @param InputInterface $input
     * @param SymfonyStyle $io
     * @return array $params
     * @throws \Exception
     */
    public function hydrateCommand(InputInterface $input, SymfonyStyle $io)
    {   
        if ($input->getOption('new_database_name'))
        {
            $new_name_database = $input->getOption('new_database_name');
        }
        else 
        {
            $new_name_database = $io->ask('Vous devez définir un nom pour la base de données que vous allez restaurer.');
        }
        
        $response = $this->loadOptionsAndParameters($input);
        $params = $response['params'];
        $params['database_name'] = false;
        
        // Il s'agit ici simplement d'utiliser un dump temporaire donc on force à non les options de zip et ftp
        $params['zip'] = 'no';
        $params['dir_dump'] = $params['dir_tmp'];
        $params['dir_fic'] = $params['dir_dump'];
        $params['new_database_name'] = Tools::cleanString($new_name_database, true);

        $params = $this->selectOne($params['connexions'], $response['fields_connexion'], $io, $response['name_connexion'], self::INDEX_CONNEXION_DB, $params);

        return $params;
    }
    
    /**
     * Permet de définir le script SQL envoyé en param
     * Ou de naviguer dans les répertoires de dump s'il n'y en a pas précisé en option
     * @param InputInterface $input
     * @param SymfonyStyle $io
     * @param array $params
     * @return array $params
     * @throws \Exception
     */
    private function loadScript(InputInterface $input, SymfonyStyle $io, array $params)
    {
        // Si le script SQL est envoyé en paramètre on le traite directement
        if ($input->getOption('script_sql'))
        {
            $params['script_sql'] = Tools::formatDirWithFile($params['dir_script_migration'], $input->getOption('script_sql'));
        }
        // Sinon on propose à l'utilisateur de le sélectionner à partir du répertoire de son choix
        else 
        {
            $tools = $this->getContainer()->get('rma.tools');
            $dir = $io->ask("Quel est le répertoire à partir duquel vous souhaitez restaurer une base de données ?" , $params['dir_dump']);
            $dumps = $tools->scanDirectory($dir);  
            // Tant que Dumps est une array c'est que l'élément sélectionné est un répertoire        
            if(count($dumps) == 0){
                throw new \Exception("Le répertoire : ". $dir ." est vide");
            }
            
            while (is_array($dumps)){
                $results = array();
                foreach ($dumps as $dump => $value){
                    // Si c'est à nouveau un folder
                    if (is_array($value)){
                        array_push($results, $dump);
                    }
                    else {
                        // On enlève le nom du fichier de synchronisation 
                        if ($value == SyncDump::NAME_DUMP){
                            continue;
                        }
                        array_push($results, $value);
                    }
                }
                $choice = $io->choice("Quel script voulez-vous executer ou dans quel répertoire souhaitez-vous vous rendre ?" , $results);
                $dir = $dir . DIRECTORY_SEPARATOR . $choice;
                if (is_dir($dir)){
                    $dumps = $tools->scanDirectory($dir);
                    // Tant que Dumps est une array c'est que l'élément sélectionné est un répertoire        
                    if(count($dumps) == 0){
                        throw new \Exception("Le répertoire : ". $dir ." est vide");
                    }
                }
                else {
                    $dumps = $dir;
                    $params['script_sql'] = $dir;
                }
            }
        }
        
        if(is_dir($params['script_sql'])){
            throw new \Exception ("Le script pour la restauration de la base de données ne peut pas être un répertoire");
        }
        
        return $params;
    }
}
