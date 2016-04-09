<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;  
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Input\InputOption;

use RMA\Bundle\DumpBundle\Factory\RDumpFactory;
use RMA\Bundle\DumpBundle\Tools\Tools;
use RMA\Bundle\DumpBundle\Command\CleanDumpCommand;

class DumpCommand extends ContainerAwareCommand {
    
    protected function configure() {
      
        $this->setName('rma:dump:database')
             ->setDescription('Permet de réaliser un dump. Option --not-all pour ne pas sauvegarder toutes les bases')
             ->addOption(
               'not-all',
               false,
               InputOption::VALUE_NONE,
               'Si not-all est spécifié, vous devrez sélectionner la base de données à dump'
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
        $helper = $this->getHelper('question');
        
        $params = array();
        $date = date('Y-m-d-H\\hi');
        $params['repertoire_name'] = $date . '__' . uniqid(); 
        $params['logger'] = $this->getContainer()->get('logger');
        
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
            'zip'           => 'Voulez-vous zipper le résultats {true, false}? ',
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

        // A gérer l'extension pour le FTP
        $params['extension'] = '.zip';
        
        // On charge les params pour le FTP
        if ($input->getOption('ftp')) 
        {
            foreach ($parametres_ftp as $parametre => $libelle)
            {
                $parametre_defaut = $this->getContainer()->getParameter('rma_'.$parametre);
                $$parametre = $parametre_defaut;
                // Si l'utilisateur a envoyé l'option i on lui pose les questions correspondantes
                if ($input->getOption('i')){
                    $question = new Question($libelle . '['.$parametre_defaut.'] ', $parametre_defaut);
                    $$parametre = $helper->ask($input, $output, $question);
                }
               $params[$parametre] = $$parametre;
            }
            
        }
        else {
            foreach ($parametres_ftp as $parametre => $libelle)
            {
                $parametre_defaut = $this->getContainer()->getParameter('rma_'.$parametre);
                $$parametre = $parametre_defaut;
                $params[$parametre] = $$parametre;
            }
        }
  
        // On gère les autres paramètres
        foreach ($parametres as $parametre => $libelle)
        {
            $parametre_defaut = $this->getContainer()->getParameter('rma_'.$parametre);
            $$parametre = $parametre_defaut;
            if ($input->getOption('i')){
                $question = new Question($libelle . '['.$parametre_defaut.'] ', $parametre_defaut);
                $$parametre = $helper->ask($input, $output, $question);
            }
           $params[$parametre] = $$parametre;
        }
        
        $params['dir_fichier'] = $dir_zip; 
     
        // On charge l'objet dump pour gérer toutes les fonctionnalités 
        $dump = RDumpFactory::create($params);
        $databases = $dump->rmaDumpGetListDatabases();          

        // On propose à l'utilisateur de sélectionner la ou les bases de données à sauvegarder
        if ($input->getOption('not-all'))
        {
            $question_dbb = new ChoiceQuestion(
                'Sélectionnez la ou les bases de données à sauvegarder (séparer par des virgules)',
                $databases,
                0
            );
            $question_dbb->setMultiselect(true);
            $question_dbb->setErrorMessage('La base de données %s est introuvable.');
            $databases = $helper->ask($input, $output, $question_dbb);
        }

        // On vérifie que la connexionDB contienne au moins 1 base de données
        if (count($databases) == 0) {
            throw new \Exception ('Aucune base de données détectée avec les paramètres définis');
        }
           
        // On charge un objet progressbar qui affichera l'avancement pour chaque base de données
        $progress = new ProgressBar($output, count($databases));
        $progress->setFormat('verbose');
        $array = array();
        
        foreach ($databases as $database) {
            $output->writeln($database . ' : ');
            $progress->advance();
            $output->writeln('');
            $output->writeln('');
            $array = $dump->rmaDumpForDatabase($database, $array);
        }
        
        $infos = $dump->rmaGetInfosDump($date, $params['dir_dump'], $params['repertoire_name'], count($databases), $array);
        
        $dump->rmaWriteDump($infos, $dir_dump);

        $progress->finish();
        $output->writeln('-----------');
        $output->writeln('Dump mis à disposition dans le répertoire ' . $dir_dump . DIRECTORY_SEPARATOR . $params['repertoire_name']);
        
        $dump->rmaDumpJustZip();
     
        if ($input->getOption('ftp')) 
        {
            $dump->rmaDepotFTP();
        }
        $output->writeln('-----------');
        $nb_jour = $this->getContainer()->getParameter('rma_nb_jour');
        CleanDumpCommand::cleanCommand($input, $output, $dir_dump, $nb_jour);
    }
}
