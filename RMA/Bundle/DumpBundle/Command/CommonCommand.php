<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

class CommonCommand extends ContainerAwareCommand {
    
    protected function configure() {
      
        $this->setName('rma:dump:help');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {             
        $io = new SymfonyStyle($input, $output);

        $io->title('Commandes disponibles dans le bundle rma :');

        $headers = array (
            'Commandes', 'Rôles', 'Options', 'Arguments'
        );
        $rows = array(
            array (
                'rma:dump:database', 
                'Cette commande permet de générer un dump',
                '--one, --i, --ftp, --name', '-all',
                '-'
            ),
            array (
                'rma:dump:clean',
                'Cette commande permet de nettoyer les dumps',
                '--nb_jour, --dir_dump, --nombre',
                '-'
            ),
            array (
                'rma:dump:sync',
                'Cette commande permet de synchroniser les métadatas',
                '--dir_dump',
                '-'
            ),
            array ('rma:dump:ftp', 
                'Cette commande permet d\'envoyer un dump par FTP',
                '-',
                '-'
            ),
            array ('rma:dump:cron', 
                'Cette commande est prévue spécialement pour être réalisée en CRON',
                '--host, --port, --user, --password, --compress, --zip, --dir_dump, ... ',
                'databases'
           )
        );
        $io->table($headers, $rows);
    }
    
    /**
     * Permet de constuire l'array $params selon les options, les parameters et les options définies par défaut
     * @param InputInterface $input
     * @return array $params
     */
    public function constructParamsArray (InputInterface $input)
    {
        $container = $this->getContainer();
        $params = array ();
        $params['date'] = date('Y-m-d-H\\hi');
        $params['repertoire_name'] = $params['date'] . '__' . uniqid();
        $params['logger'] = $container->get('logger');
        $params['extension'] = '.zip';
        
        $parameters_enables = array (
            'nb_jour'               => 5, 
            'nombre_dump'           => 10, 
            'dir_dump'              => 'web/dump', 
            'dir_export'            => 'web/export',
            'dir_tmp'               => 'web/tmp',
            'dir_script_migration'  => 'web/script',
            'excludes'              => array ('performance_schema'), 
            'ftp_ip'                => '127.0.0.1', 
            'ftp_port'              => '21',
            'ftp_username'          => 'rma',
            'ftp_password'          => 'rma_password',
            'ftp_timeout'           => 90,
            'ftp_path'              => '/home/rma/dump',
            'host'                  => 'localhost', 
            'port'                  => '3306', 
            'user'                  => "root", 
            'password'              => "none", 
            'compress'              => "none", 
            'zip'                   => "no", 
            'ftp'                   => "no",
            'dir_zip'               => "web/zip",
            'excludes'              => array('mysql', 'information_schema', 'performance_schema'),
            'name'                  => "name_database"
        );
        
        $parameters_doctrine = array('host' , 'port', 'user', 'password', 'name');
        
        foreach ($parameters_enables as $parameter_enable => $default)
        {
            // On vérifie si l'utilisateur a défini un paramètre custom dans le paramters.yml pour rma
            if($container->hasParameter('rma_'.$parameter_enable))
            {
                $$parameter_enable = $container->getParameter('rma_'.$parameter_enable);
            } 
            // Sinon on prend les parameters définis au niveau de doctrine, s'ils existent
            else if (in_array($parameter_enable, $parameters_doctrine) && $container->hasParameter('database_'. $parameter_enable))
            {
                $$parameter_enable = $container->getParameter('database_'. $parameter_enable);
            }
            // Sinon on les initialise avec les valeurs par défaut
            else 
            {
                $$parameter_enable = $default;
            }
            $params[$parameter_enable] = $$parameter_enable;
        }

        $params = $this->loadOptions($input, $params);
        return $params;
    }
    
    /**
     * Permet selon les options de load les paramètres correspondants
     * @param InputInterface $input
     * @param array $params
     * @return array $params
     */
    public function loadOptions(InputInterface $input, Array $params)
    {
        $rOptions = $input->getOptions();
        $container = $this->getContainer();
     
        foreach ($rOptions as $rOption => $rvalue)
        {
            if($container->hasParameter('rma_'.$rOption))
            {
                $$rOption = $container->getParameter('rma_'.$rOption);
            }

            // On vérifie si une valeur a été transmise en option. Si c'est le cas on surcharge le parameters
            if (!is_null($rvalue))
            {
                $$rOption = $rvalue;
            }
            if (isset($$rOption))
            {
                 $params[$rOption] = $$rOption;
            }
        }
        return $params;
    }
    
    /**
     * Permet de poser une série de questions à l'utilisateur
     * @param InputInterface $input
     * @param array $params
     * @param array $parametersWithQuestions
     * @param SymfonyStyle $io
     * @return array $params
     */
    public function rmaAskQuestions(InputInterface $input, Array $params, Array $parametersWithQuestions, SymfonyStyle $io)
    {
        foreach ($parametersWithQuestions as $parametre => $libelle)
        {
            $parametre_defaut = $params[$parametre];
            if ($input->getOption('i')){
                $params[$parametre] = $io->ask($libelle , $parametre_defaut);
            }
        }
        return $params;
    }
   
    /**
     * Permet de lancer la commande de zip
     * @param type $dump
     * @param SymfonyStyle $io
     */
    public function zipCommand($dump, SymfonyStyle $io)
    {
        $io->title('Compression du résultat');
        $dump->rmaDumpJustZip();
        $io->success('Compression réussie');
    }
}
