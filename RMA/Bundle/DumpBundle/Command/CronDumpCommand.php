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
                ->addArgument('databases',InputArgument::IS_ARRAY,'Les bases de données à sauvegarder séparés par des espaces.');       
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {        
        $params = $this->constructParamsArray($input);
        $io = new SymfonyStyle($input, $output);
        
        // A gérer l'extension pour le FTP   - A retirer par la suite 
        $params['dir_fichier'] = $params['dir_zip']; 
        
        // On gère le mot de passe pouvant être vide 
        if(($input->getOption('password'))== 'none' || $params['password'] == 'none')
        {
            $params['password'] = '';
        }

        SyncDumpCommand::syncCommand($io, $params);
        
        $dump = RDumpFactory::create($params);
        
        $databases = $dump->rmaDumpGetListDatabases();

        if ($input->getArgument('databases'))
        {
            $databases = $input->getArgument('databases');
        }
        
        DumpCommand::dumpDatabases($io, $databases, $dump, $output);
        
        FtpCommand::saveDumpInFtp($io, $dump, $params);

        CleanDumpCommand::cleanCommand($io, $params);
    }
}
