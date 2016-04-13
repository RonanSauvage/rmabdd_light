<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

use RMA\Bundle\DumpBundle\Factory\RDumpFactory;
use RMA\Bundle\DumpBundle\Tools\Tools;
use RMA\Bundle\DumpBundle\Command\CommonCommand;

class DumpCommand extends CommonCommand {
    
    protected function configure() {
      
        $this->setName('rma:dump:database')
             ->setDescription('Permet de réaliser un dump. Option --not-all pour ne pas sauvegarder toutes les bases')
             ->addOption(
               'one',
               false,
               InputOption::VALUE_NONE,
               'Si one est spécifié, vous devrez sélectionner la base de données à dump'
            )
            ->addOption(
               'i',
               false,
               InputOption::VALUE_NONE,
               'Si i est spécifié, vous aurez des intéractions pour sélectionner les données à dump. Sinon les parameters sera pris. '
            )
            ->addOption(
               'ftp',
               false,
               InputOption::VALUE_NONE,
               'Si ftp est spécifié, le dump sera sauvegardé sur le serveur FTP défini en paramètre ou dans les interactions avec i. '
            )
            ->addOption(
               'name',
               false,
               InputOption::VALUE_REQUIRED,
               'Permet de définir un nom pour le dossier de sauvegarde. '
            );      
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {          
        $container = $this->getContainer();
        $io = new SymfonyStyle($input, $output);
        $params = $this->constructParamsArray($input);

        if ($input->getOption('name'))
        {
            $name_rep =  Tools::cleanString($input->getOption('name')) ;
            $params['repertoire_name'] = $name_rep . '__' . uniqid();
        }

        $parametres = array(
            'host'          => 'Veuillez renseigner l\'ip de votre connexion : ',
            'port'          => 'Veuillez renseigner le port : ',
            'username'      => 'Veuillez renseigner le username utilisé : ',
            'password'      => 'Veuillez renseigner le password : ',
            'compress'      => 'Voulez-vous compression les dumps {none, gzip, bzip2}  ? ',
            'zip'           => 'Voulez-vous zipper le résultats {yes, no}? ',
            'dir_dump'      => 'Veuillez renseigner le dossier dans lequel sauvegarder les dump : ',
            'dir_zip'       => 'Veuillez renseigner le dossier dans lequel sauvegarder les zip : ',
        );
        
        $parametres_ftp = array(
            'ftp_ip'        => 'Veuillez renseigner l\'ip de connexion au ftp : ',
            'ftp_username'  => 'Veuillez renseigner l\'username du ftp utilisé : ',
            'ftp_password'  => 'Veuillez renseigner le password du ftp utilisé : ',
            'ftp_port'      => 'Veuillez renseigner le port du ftp utilisé : ',
            'ftp_timeout'   => 'Veuillez renseigner le timeout du ftp utilisé : ',
            'ftp_path'      => 'Veuillez renseigner le path du ftp utilisé : '
        );
        
        // On charge les params pour le FTP
        if ($input->getOption('ftp')) 
        {
            $params['ftp'] = 'yes';
            foreach ($parametres_ftp as $parametre => $libelle)
            {
                $parametre_defaut = $container->getParameter('rma_'.$parametre);
                $$parametre = $parametre_defaut;
                // Si l'utilisateur a envoyé l'option i on lui pose les questions correspondantes
                if ($input->getOption('i')){
                    $$parametre = $io->ask($libelle . '['.$parametre_defaut.'] ', $parametre_defaut);
                }
               $params[$parametre] = $$parametre;
            }
            
        }
        else {
            foreach ($parametres_ftp as $parametre => $libelle)
            {
                $parametre_defaut = $container->getParameter('rma_'.$parametre);
                $$parametre = $parametre_defaut;
                $params[$parametre] = $$parametre;
            }
        }
  
        // On gère les autres paramètres
        foreach ($parametres as $parametre => $libelle)
        {
            $parametre_defaut = $container->getParameter('rma_'.$parametre);
            $$parametre = $parametre_defaut;
            if ($input->getOption('i')){
                $$parametre = $io->ask($libelle , $parametre_defaut);
            }
           $params[$parametre] = $$parametre;
        }
      
        if ($params['password'] == 'none')
        {
            $params['password'] = '';
        }
     
        $params['dir_fichier'] = $dir_zip; 
        
        // On charge l'objet dump pour gérer toutes les fonctionnalités 
        $dump = RDumpFactory::create($params);
        $databases = $dump->rmaDumpGetListDatabases($params['excludes']);          

        // On propose à l'utilisateur de sélectionner la ou les bases de données à sauvegarder
        if ($input->getOption('one'))
        {
            $databases = $io->choice('Sélectionnez la base de données à sauvegarder', $databases);
        }
           
        SyncDumpCommand::SyncCommand($io, $params['dir_dump'], $params['logger']);
        
        // On charge un objet progressbar qui affichera l'avancement pour chaque base de données
        $io->title('Dump des ' . count($databases) . ' base(s) de données');
        $io->progressStart(count($databases));

        $logs = array();
        
        if ($input->getOption('one'))
        {
            $io->progressAdvance();
            $logs = $dump->rmaDumpForDatabase($databases, $logs);
        }
        else 
        {
            foreach ($databases as $database) {
                $io->progressAdvance();
                $logs = $dump->rmaDumpForDatabase($database, $logs);
            }
        }

        $infos = $dump->rmaGetInfosDump($params['date'], $params['dir_dump'], $params['repertoire_name'], count($databases), $logs);
        $dump->rmaWriteDump($infos, $params['dir_dump']);

        $io->progressFinish();

        $io->success('Dump mis à disposition dans le répertoire ');

        // On zip le dump
        $dump->rmaDumpJustZip();

        // On sauvegarde le dump sur le serveur FTP
        FtpCommand::saveDumpInFtp($params['ftp'], $dump);

        // On lance l'action de suppression des anciens dumps
        CleanDumpCommand::cleanCommand($io, $params['dir_dump'], $params['nb_jour'], $params['logger']);
    }
}
