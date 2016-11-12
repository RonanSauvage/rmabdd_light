<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;

use RMA\Bundle\DumpBundle\Factory\RDumpFactory;
use RMA\Bundle\DumpBundle\Command\CommonCommand;

class DumpCommand extends CommonCommand {
    
    protected function configure() {
      
        $this->setName('rma:dump:database')
            ->setDescription('Permet de réaliser un dump.')
            ->addOption('one', null, InputOption::VALUE_NONE, 'Si one est spécifié, vous devrez sélectionner la base de données à dump')
            ->addOption('i', null, InputOption::VALUE_NONE, 'Si i est spécifié, vous aurez des intéractions pour sélectionner les données à dump')
            ->addOption('ftp', false, InputOption::VALUE_NONE, 'Si ftp est spécifié, le dump sera sauvegardé sur le serveur FTP défini en paramètre ou dans les interactions avec i')
            ->addOption('repertoire_name', null, InputOption::VALUE_REQUIRED, 'Permet de définir un nom pour le dossier de sauvegarde. ', null)
            ->addOption('all', null, InputOption::VALUE_NONE, 'Si all est spécifié, exporte toutes les bases quelque soit les paramèters définis')
            ->addArgument('databases',InputArgument::IS_ARRAY,'Les bases de données à sauvegarder séparées par des espaces.')
            ->setAliases(['dump']);       
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
      
        // Si l'option all est envoyée, on ne gère pes les parameters ni les autres options
        if (!$input->getOption('all')){
           // On propose à l'utilisateur de sélectionner la base de données à sauvegarder avec l'option one
            if ($input->getOption('one'))
            {
                $databases = array($io->choice('Sélectionnez la base de données à sauvegarder', $databases));
            }

            // On gère la base de données définie dans le parameters
            if ($params['name'] && !$input->getOption('one') && $params['name'] != "name_database")
            {
                $databases = array($params['name']);
            }
            
            // On permet la saisie du nom des bases de données en arguments
            if ($input->getArgument('databases'))
            {
                $databases = $input->getArgument('databases');
            }
        }   
        
        // On synchronise le fichier de logs des dumps 
        SyncDumpCommand::syncCommand($io, $params);
        
        // On lance la commande de dump
        DumpCommand::dumpDatabases($io, $databases, $dump, $output);
        
        // On zip le dump
        $dump->rmaDumpJustZip();

        // On sauvegarde le dump sur le serveur FTP
        FtpCommand::saveDumpInFtp($io, $dump, $params);

        // On lance l'action de suppression des anciens dumps
        CleanDumpCommand::cleanCommand($io, $params);
    }
    
    public function hydrateCommand(InputInterface $input, $io)
    {
        $response = $this->loadOptionsAndParameters($input);
        $params = $response['params'];

        // On charge les paramètres et les questions correspondantes dans le cas où l'utilisateur demande de l'intéraction
        $parametres = array(
            'repertoire_name'   => 'Veuillez donner un nom à votre dump : ',
            'host'              => 'Veuillez renseigner l\'ip de votre connexion : ',
            'port'              => 'Veuillez renseigner le port : ',
            'user'              => 'Veuillez renseigner le username utilisé : ',
            'password'          => 'Veuillez renseigner le password : ',
            'compress'          => 'Voulez-vous compression les dumps {none, gzip, bzip2}  ? ',
            'zip'               => 'Voulez-vous zipper le résultats {yes, no}? ',
            'dir_dump'          => 'Veuillez renseigner le dossier dans lequel sauvegarder les dump : ',
            'dir_zip'           => 'Veuillez renseigner le dossier dans lequel sauvegarder les zip : ',
        );
        
        $parametres_ftp = array(
            'ftp_ip'        => 'Veuillez renseigner l\'ip de connexion au ftp : ',
            'ftp_username'  => 'Veuillez renseigner l\'username du ftp utilisé : ',
            'ftp_password'  => 'Veuillez renseigner le password du ftp utilisé : ',
            'ftp_port'      => 'Veuillez renseigner le port du ftp utilisé : ',
            'ftp_timeout'   => 'Veuillez renseigner le timeout du ftp utilisé : ',
            'ftp_path'      => 'Veuillez renseigner le path du ftp utilisé : '
        );
        
        // Si l'utilisateur souhaité pouvoir saisir directement en ligne de commande ses identifiants
        if ($input->getOption('i')) {
            $params = $this->rmaAskQuestions($input, $params, $parametres, $io); 
            if($input->getOption('ftp')){
                $params = $this->rmaAskQuestions($input, $params, $parametres_ftp, $io);
            }
        }
        else {
            $params = $this->selectOne($params['connexions'], $response['fields_connexion'], $io, $response['name_connexion'], $params);
            if($input->getOption('ftp')){
                $params = $this->selectOne($params['ftps'], $response['fields_ftp'], $io, $response['name_ftp'], $params);            
            }
        }
     
        if (isset($params['password']) && $params['password'] == 'none')
        {
            $params['password'] = '';
        }
     
        $params['dir_fic'] = $params['dir_zip'];
        return $params;
    }
    
    /**
     * Permet de lancer un dump de la database
     * @param SymfonyStyle $io
     * @param array $databases
     * @param DumpInterface $dump
     * @param OutputInterface $output
     */
    public static function dumpDatabases(SymfonyStyle $io, Array $databases, $dump, OutputInterface $output)
    {
        $number_databases = count($databases);
         // On charge un objet progressbar qui affichera l'avancement pour chaque base de données
        $io->title('Dump des ' . $number_databases . ' base(s) de données');
      
        // On charge la progressbar et défini son format notamment pour afficher le nom de la base de données à chaque advance
        $progress = new ProgressBar($output, $number_databases);
        $progress->start();
        $progress->setRedrawFrequency(1);
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s% %message% ');

        $logs = array();
        
        foreach ($databases as $database)
        {
            $progress->setMessage($database);
            $logs = $dump->rmaDumpForDatabase($database, $logs); 
            $progress->advance();
        }

        $progress->finish();
        $io->newLine(2);
        $infos = $dump->rmaGetInfosDump($number_databases, $logs);
        $dump->rmaWriteDump($infos);

        $io->success('Dump mis à disposition dans le répertoire ');
    } 
}
