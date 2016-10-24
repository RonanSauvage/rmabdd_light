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
           ),
            array ('rma:dump:export', 
                "Cette commande permet d'automatiser des scripts sur une base de données en l'exportant",
                '--script, --repertoire_name, --keep_tmp, --name_database_temp',
                '-'
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
            'driver'                => 'pdo_mysql',
            'host'                  => 'localhost', 
            'port'                  => '3306', 
            'user'                  => "root", 
            'password'              => "none", 
            'nb_jour'               => 5, 
            'nombre_dump'           => 10, 
            'dir_dump'              => 'web/dump', 
            'dir_export'            => 'web/export',
            'dir_tmp'               => 'web/tmp',
            'dir_script_migration'  => 'web/script',
            'ftp_ip'                => '127.0.0.1', 
            'ftp_port'              => '21',
            'ftp_username'          => 'rma',
            'ftp_password'          => 'rma_password',
            'ftp_timeout'           => 90,
            'ftp_path'              => '/home/rma/dump',          
            'compress'              => "none", 
            'zip'                   => "no", 
            'ftp'                   => "no",
            'dir_zip'               => "web/zip",
            'name'                  => "name_database",
            'keep_tmp'              => "no",
            'script'                => 'rma_default_file.sql'
        );

        foreach ($parameters_enables as $parameter_enable => $default)
        {
            // On vérifie si l'utilisateur a défini un paramètre custom dans le paramters.yml pour rma
            if($container->hasParameter('rma_'.$parameter_enable))
            {
                $$parameter_enable = $container->getParameter('rma_'.$parameter_enable);
            } 
            else 
            {
                $$parameter_enable = $default;
            }
            $params[$parameter_enable] = $$parameter_enable;
        }
        $params = $this->loadConnexions($input, $params);
        
        return $this->loadOptions($input, $params);
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
            // Pour les options sans valeur, false est défini par défaut dans le container. Nos options fonctionnent avec des strings donc false correspond à pas de valeur
            if (!is_null($rvalue) && ($rvalue))
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
     * Permet de charger les différentes connexions configurées
     * @param InputInterface $input
     * @param array $params
     * @return array $params
     */
    public function loadConnexions(InputInterface $input, Array $params){
        $container = $this->getContainer();
        
        $excludes = array('performance_schema', 'mysql');
        
        $connexions = array();
        $params_connexion = array(
            'driver'    => 'pdo_mysql',
            'host'      => 'localhost',
            'port'      => '3306',
            'name'      => 'ConnexionName',
            'user'      => 'root',
            'password'  => '' ,
            'excludes'  => $excludes
        );
       
        // Si une config est définie au niveau de Doctrine, on la charge 
        $parameters_doctrine = array('driver', 'host' , 'port', 'user', 'password', 'name');
        foreach ($parameters_doctrine as $parameter_doctrine){        
            if ($container->hasParameter('database_'. $parameter_doctrine))
            {   
                $connexions['Doctrine'][$parameter_doctrine] = $container->getParameter('database_'. $parameter_doctrine);
                // règle spécifique pour le mot de passe vide
                if ($parameter_doctrine == 'password'){
                    $connexions['Doctrine']['password'] = 'none';
                }
            }
        }
        // Si il y a une connexion définie pour doctrine, on récupère les excludes définies pour la connexion
        if (isset($connexions['Doctrine'])){
            if ($container->hasParameter('rma_excludes'))
            {
                $connexions['Doctrine']['excludes'] = array_merge($excludes, $container->getParameter('rma_excludes'));
            }
            else {
                 $connexions['Doctrine']['excludes'] = $excludes;
            }
        }
        
         // On charge l'array Connexions avec les autres connexions définies en parameters
        if ($container->hasParameter('rma_connexion')){
            $rma_connexions = $container->getParameter('rma_connexion');
            foreach ($rma_connexions as $rma_connexion){
                foreach ($rma_connexion as $key => $value){
                    $connexions[$rma_connexion['rma_name']][substr($key,4)] = $value;        
                    if(isset($rma_connexion['rma_exclude'])){
                        $excludes_sup = $rma_connexion['rma_exclude'];
                        $excludes_with_sup = array_merge($excludes, $excludes_sup);
                        $rma_connexion['rma_exclude'] = $excludes_with_sup;
                    }
                    else {
                        $rma_connexion['rma_exclude'] = $excludes;
                    }
                }
              
                // On vérifie s'il manque des paramètres par rapport aux obligatoires pour une connexion
                $params_missing = array_diff_ukey($params_connexion, $rma_connexion, array (self::class,'compareKeys')); 
           
                // Pour tous les paramètres manquants on définit la valeur par dé<fa></fa>ut  
                foreach($params_missing as $param_missing => $value){
                    $connexions[$rma_connexion['rma_name']][$param_missing] = $params_connexion[$param_missing];
                }
            }
        }
        $params['connexions'] = $connexions;

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
    
    /**
     * Permet de proposer à l'utilisateur la sélection de sa connexion s'il y en a plusieurs
     * @param array $params
     * @param SymfonyStyle $io
     * @return array $params
     * @throws \Exception
     */
    public function selectConnexion(array $params, SymfonyStyle $io){
      
        if(count($params['connexions']) == 0){
            Throw new \Exception ('Vous devez définir au moins une configuration base de données.');
        }
        else if(count($params['connexions']) > 1){
            $connexion = array($io->choice('Sélectionnez la connexion que vous souhaitez utiliser', array_keys($params['connexions'])));
            $connexionSelected = $params['connexions'][$connexion[0]];
            
            $params['password'] = $connexionSelected['password'];
            $params['driver'] = $connexionSelected['driver'];
            $params['host'] = $connexionSelected['host'];
            $params['user'] = $connexionSelected['user'];
            $params['port'] = $connexionSelected['port'];
            if (isset($connexionSelected['excludes'])){
                $params['excludes'] = $connexionSelected['excludes'];
            }
        }
        else {
            $name =  array_keys($params['connexions']);
            $params['password'] = $params['connexions'][$name[0]]['password'];
            $params['driver'] = $params['connexions'][$name[0]]['driver'];
            $params['host'] = $params['connexions'][$name[0]]['host'];
            $params['user'] = $params['connexions'][$name[0]]['user'];
            $params['port'] = $params['connexions'][$name[0]]['port'];
            if (isset($params['connexions'][$name[0]]['excludes'])){
                $params['excludes'] = $params['connexions'][$name[0]]['excludes'];
            }
            else {
                $params['excludes'] = array('performance_schema', 'mysql');
            }
        }
        return $params;
    }

    /**
    * Permet de comparer les keys de deux chaînes en appliquant un traitement sur les key de la deuxième chaînes
    * @param : string $key1
    * @param : string $key2
    * @return : -1 si différent 0 si similaire
    */
    public static function compareKeys($key1, $key2){
        $keyAfterTreatment = substr($key2, 4);
        if ($keyAfterTreatment != $key1){
            return -1;
        }
        return 0;
    }
}
