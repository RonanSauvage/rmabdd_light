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
            );
                
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {          
        $helper = $this->getHelper('question');
        
        $params = array();
        $params['repertoire_name'] = date('Y-m-d-H\\hi') . '__' . uniqid(); 
        
        $parametres = array(
            'host'          => 'Veuillez renseigner l\'ip de votre connexion : ',
            'port'          => 'Veuillez renseigner le port : ', 
            'username'      => 'Veuillez renseigner le username utilisé : ',
            'password'      => 'Veuillez renseigner le password : ',
            'compress'      => 'Voulez-vous compression les dumps {none, gzip, bzip2}  ? ',
            'zip'           => 'Voulez-vous zipper le résultats {true, false}? ',
            'dir_dump'      => 'Veuillez renseigner le dossier dans lequel sauvegarder les dump : ',
            'dir_zip'       => 'Veuillez renseigner le dossier dans lequel sauvegarder les zip : '
        );
        
        $parametres_ftp = array(
                'ftp_ip'        => 'Veuillez renseigner l\'ip de connexion au ftp : ',
                'ftp_username'  => 'Veuillez renseigner l\'username du ftp utilisé : ',
                'ftp_password'  => 'Veuillez renseigner le password du ftp utilisé : ',
                'ftp_port'      => 'Veuillez renseigner le port du ftp utilisé : ',
                'ftp_timeout'   => 'Veuillez renseigner le timeout du ftp utilisé : ',
                'ftp_path'      => 'Veuillez renseigner le path du ftp utilisé : '
            );

        $params['extension'] = '.zip';
        
        if ($input->getOption('ftp')) 
        {
             foreach ($parametres_ftp as $parametre => $libelle)
            {
                $parametre_defaut = $this->getContainer()->getParameter('rma_'.$parametre);
                $$parametre = $parametre_defaut;
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
            throw new Exception ('Aucune base de données détectée avec les paramètres définis');
        }
           
        $progress = new ProgressBar($output, count($databases));
        $progress->setFormat('verbose');
            
        foreach ($databases as $database) {
            $output->writeln($database . ' : ');
            $progress->advance();
            $output->writeln('');
            $output->writeln('');
            $dump->rmaDumpForDatabase($database);
        }
        
        $progress->finish();
        $output->writeln('');
        $output->writeln('Dump mis à disposition dans le répertoire ' . $dir_dump . DIRECTORY_SEPARATOR . $params['repertoire_name']);
        
        $dump->rmaDumpJustZip();
        
        if ($input->getOption('ftp')) 
        {
            $dump->rmaDepotFTP();
        }
    }
}
