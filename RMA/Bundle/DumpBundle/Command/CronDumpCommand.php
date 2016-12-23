<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

use RMA\Bundle\DumpBundle\Factory\RDumpFactory;
use RMA\Bundle\DumpBundle\Command\CommonCommand;

class CronDumpCommand extends CommonCommand {
    
    protected function configure() {
      
        $this->setName('rma:dump:cron')
                ->setDescription('Permet de réaliser un dump en crontab. Si vous ne mettez pas d\'argument toutes les bases de données seront sauvegardées.')
                ->addOption('host', '', InputOption::VALUE_REQUIRED, 'L\'ip de la connexion à la base de données.')
                ->addOption('port', '', InputOption::VALUE_REQUIRED, 'Le port d\'accès à la base de données.')
                ->addOption('user', '', InputOption::VALUE_REQUIRED, 'L\'username de connexion à la base de données.')
                ->addOption('password', '', InputOption::VALUE_REQUIRED, 'Le password de connexion.')
                ->addOption('compress', '', InputOption::VALUE_REQUIRED, 'Permet de compresser les dumps')
                ->addOption('zip', '', InputOption::VALUE_OPTIONAL, 'Permet de zipper le résultat du dump')
                ->addOption('dir_dump', '', InputOption::VALUE_REQUIRED, 'Le lien vers le dossier de dump')
                ->addOption('dir_zip', '', InputOption::VALUE_REQUIRED, 'Le lien vers le dossier de zip')
                ->addOption('ftp', '', InputOption::VALUE_OPTIONAL, 'Permet de transférer le dump en FTP')
                ->addOption('ftp_ip', '', InputOption::VALUE_REQUIRED,'Ip du FTP')
                ->addOption('ftp_username', '', InputOption::VALUE_REQUIRED,'Username pour le FTP')
                ->addOption('ftp_password', '', InputOption::VALUE_REQUIRED, 'Mot de passe pour le ftp')
                ->addOption('ftp_port', '', InputOption::VALUE_REQUIRED, 'Le port pour le FTP')
                ->addOption('ftp_timeout', '', InputOption::VALUE_REQUIRED, 'Le timeout paramétré pour le FTP')
                ->addOption('ftp_path','',InputOption::VALUE_REQUIRED,'Le path pour la sauvegarde sur le FTP')
                ->addOption('all', '',InputOption::VALUE_NONE,'Permet de sauvegarder toutes les bases de données')
                ->addOption('connexion', '', InputOption::VALUE_REQUIRED,'Permet de spécifier une connexion spécifique')
                ->addArgument('databases',InputArgument::IS_ARRAY,'Les bases de données à sauvegarder séparées par des espaces.');       
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {       
        $io = new SymfonyStyle($input, $output);
        $container = $this->getContainer();
        
        $response = $this->loadOptionsAndParameters($input);
        $params = $response['params'];
        
        if($input->hasOption('connexion') && $input->getOption('connexion') != ''){
            $nameConnexion = $input->getOption('connexion');
            if(isset($params['connexions'][$nameConnexion])){
                $connexion = $params['connexions'][$nameConnexion];
                $params['connexions'] = array();
                $params['connexions'][0] = $connexion;
            }
            else {
                throw new \exception("La connexion envoyée en option " . $nameConnexion . " est introuvable");
            }
        }
        else if(isset($params['connexions']['Doctrine'])){
            $doctrineConnexion = $params['connexions']['Doctrine'];
            $params['connexions'] = array();
            $params['connexions'][0] = $doctrineConnexion;
        }
        else {
            throw new \Exception("Vous devez définir une connexion custom ou insérer dans votre parameters les paramètres de Doctrine");
        }
        
        $params = $this->selectOne($params['connexions'], $response['fields_connexion'], $io, $response['name_connexion'], $params);
        
        if (isset($params['password']) && $params['password'] == 'none')
        {
            $params['password'] = '';
        }
        $params['dir_fic'] = $params['dir_zip'];
        
        // On gère le mot de passe pouvant être vide 
        if(($input->getOption('password'))== 'none' || $params['password'] == 'none')
        {
            $params['password'] = '';
        }

        SyncDumpCommand::syncCommand($io, $params);
        
        $dump = RDumpFactory::create($params);
     
        $allDatabases = $dump->rmaDumpGetListDatabases();
        // Si l'option all est spécifiée, on lance l'export de toutes les bases
        if($input->getOption('all'))
        {
            $databases = $allDatabases;
        }
        // On gère les bases fournies en paramètres
        else if ($input->getArgument('databases'))
        {
            $databases = $input->getArgument('databases');
        }
        elseif ($container->hasParameter('database_name')){
            $database = $container->getParameter('database_name');
            if(in_array($database, $allDatabases)){
                $databases = array($database);
            }
            else {
                throw new \Exception('La base de données enregistré dans les paramètres Doctrine '. $database . "n'est pas disponible");
            }
        }
        else {
            throw new \Exception('La base de données à dump doit être spécifiée pour la commande CRON.');
        }
        
        DumpCommand::dumpDatabases($io, $databases, $dump, $output);
        
        FtpCommand::saveDumpInFtp($io, $dump, $params);

        CleanDumpCommand::cleanCommand($io, $params);
    }
}
