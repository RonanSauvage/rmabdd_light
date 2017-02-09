<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use RMA\Bundle\DumpBundle\ConnexionDB\ConnexionDB;
use RMA\Bundle\DumpBundle\Ftp\Rftp;
use RMA\Bundle\DumpBundle\Tools\Tools;

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
                'rma:dump:database alias dump', 
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
            array ('rma:dump:export alias export', 
                "Cette commande permet d'automatiser des scripts sur une base de données en l'exportant",
                '--script, --repertoire_name, --keep_tmp, --name_database_temp, --ftp',
                '-'
           ),
           array ('rma:dump:inspectFtps alias inspectFtps', 
                "Cette commande permet de visualiser les différentes configurations FTP définies dans le parameters",
                '-',
                '-'
           ),
           array ('rma:dump:inspectConnexions alias inspectConnexions', 
                "Cette commande permet de visualiser les différentes configurations de connexion aux bases de données définies dans le parameters",
                '-',
                '-'
           ),
           array ('rma:restaure:database alias restaure', 
                "Cette commande permet de restaurer une base de données à partir d'un script SQL",
                '--new_database_name, --script_sql',
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
    public function constructParamsArray (InputInterface $input, Array $fields = array())
    {
        $container = $this->getContainer();
        $params = array ();
        $params['date'] = date('Y-m-d-H\\hi');
        $params['repertoire_name'] = $params['date'] . '__' . uniqid();
        $params['logger'] = $container->get('logger');
        $params['extension'] = '.zip';
        $parameters_enables = array (
            'nb_jour',               
            'nombre_dump',          
            'dir_dump',             
            'dir_export',           
            'dir_tmp',              
            'dir_script_migration',          
            'compress',              
            'zip',                  
            'ftp',                   
            'dir_zip',                            
            'keep_tmp',             
            'script'                
        );
  
        foreach ($parameters_enables as $parameter_enable)
        {
            // On vérifie si l'utilisateur a défini un paramètre custom dans le paramters.yml pour rma
            if($container->hasParameter('rma_'.$parameter_enable)) {
                $$parameter_enable = $container->getParameter('rma_'.$parameter_enable);
            } 
            $params[$parameter_enable] = $$parameter_enable;
        }

        
        $params['name'] = "name_database";
        
        foreach ($fields as $object => $fields_object){
            $load = 'load'.$object;
            $params = $this->$load($params, $fields_object);
        }
        
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
     * @param array $params
     * @param array $fields
     * @return array $params
     */
    public function loadConnexions(Array $params, Array $fields){
        $container = $this->getContainer();
        
        $connexions = array();
       
        // Si une config est définie au niveau de Doctrine, on la charge 
        foreach ($fields as $field => $defaultValue){ 
            if ($container->hasParameter('database_'. $field))
            {   
                $connexions['Doctrine'][$field] = $container->getParameter('database_'. $field);
            }
            elseif($container->hasParameter('rma_'.$field))
            {
                $connexions['Doctrine'][$field] = $container->getParameter('rma_'. $field);
            }         
        }
        // Si il y a une connexion définie pour doctrine, on récupère les excludes définies pour la connexion
        if (isset($connexions['Doctrine'])){
            if ($container->hasParameter('rma_excludes'))
            {
                $connexions['Doctrine']['excludes'] = array_merge($fields['excludes'], $container->getParameter('rma_excludes'));
            }
            else {
                 $connexions['Doctrine']['excludes'] = $fields['excludes'];
            }
        }
        
        $params['connexions'] = $this->loadArrayToParams('connexions', $fields, $connexions);
         
        return $params;
    }

    /**
     * Permet de load les paramètres pour les connexion FTPs
     * @param array $params
     * @param array $fields
     * @return array $params
     */
    public function loadFtps(Array $params, Array $fields){
        $params['ftps'] = $this->loadArrayToParams('ftps', $fields);
        return $params;
    }

    /**
    * Permet de charger les paramètres définis au niveau du parameters sous forme d'array
    * @param : string $object_name
    * @param : array $fields 
    * @param : array $results (préalablement remplie par des configurations custom supplémentaires)
    * @return : array $results
    */
    public function loadArrayToParams($object_name, Array $fields, Array $results = array())
    {
        $object_name_with_prefix = 'rma_' . $object_name;
        $container = $this->getContainer();
        if ($container->hasParameter($object_name_with_prefix)){
            $arrays_params_for_object = $container->getParameter($object_name_with_prefix);
            if(is_array($arrays_params_for_object)){
                foreach ($arrays_params_for_object as $array_params_for_object){
                    foreach ($array_params_for_object as $key => $value){
                        $results[$array_params_for_object['rma_name_'. substr($object_name, 0, -1)]][substr($key,4)] = $value;        
                    }
                
                    // On vérifie s'il manque des paramètres par rapport aux obligatoires
                    $params_missing = array_diff_ukey($fields, $array_params_for_object, array (self::class,'compareKeys')); 
            
                    // Pour tous les paramètres manquants on définit la valeur par défaut  
                    foreach($params_missing as $param_missing => $value){
                            $results[$array_params_for_object['rma_name_'. substr($object_name, 0, -1)]][$param_missing] = $fields[$param_missing]; 
                    }
                }
            } 
            else {
                throw new \Exception("Le paramètre " . $object_name_with_prefix . ' défini dans le parameters.yml doit être une array');
            }
        }
        return $results;
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
    * Permet de sélectioner un choix selon différentes configurations
    * @param array $choices
    * @param array $fields
    * @param SymfonyStyle $io
    * @param string $name
    * @param array $params
    * @return array $params
    */
    public function selectOne(array $choices, array $fields, SymfonyStyle $io, $name, array $params){
        if(count($choices) == 0){
            Throw new \Exception ('Vous devez définir au moins une configuration pour ' . $name);
        }
        // S'il existe plusieurs configurations on laisse choisir l'utilisateur
        else if(count($choices) > 1){
            $choice = array($io->choice('Sélectionnez la configuration que vous souhaitez utiliser', array_keys($choices)));
            $choiceSelected = $choices[$choice[0]];
            foreach ($fields as $field => $default){
                if(isset($choiceSelected[$field])){
                    $params[$field] = $choiceSelected[$field];
                }
                else {
                    $params[$field] = $choiceSelected[$field][$default];
                }  
            }
        }
        // S'il n'existe qu'une configuration
        else {
            $array_keys =  array_keys($choices);
            foreach ($fields as $field => $value){
                 if(isset($choices[$array_keys[0]][$field]) || is_null($choices[$array_keys[0]][$field])){
                    $params[$field] = $choices[$array_keys[0]][$field];
                 }  
                 else {
                    $params[$field] = $value;
                 }            
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
    
    /**
     * Permet d'hydrater les commandes
     * @param InputInterface $input
     * @return array $response
     */
    public function loadOptionsAndParameters(InputInterface $input){
        $fields = array();
        $fields['Ftps'] = array();
        $fields['Connexions']  = ConnexionDB::getFields();   
        
        if($input->hasOption('ftp') && $input->getOption('ftp')){
             $fields['Ftps'] = Rftp::getFields();
             $params['ftp'] = 'yes'; 
        }        
        
        $params = $this->constructParamsArray($input, $fields);
        $name_connexion = 'connexion base de données';   
        $name_ftp = 'connexion au serveur ftp';  
        
        if ($input->hasOption('repertoire_name') && $input->getOption('repertoire_name')) {
            $name_rep =  Tools::cleanString($input->getOption('repertoire_name')) ;
            $params['repertoire_name'] = $name_rep . '__' . uniqid();
        }
        
        $response = array(
            'params'            => $params, 
            'name_connexion'    => $name_connexion, 
            'name_ftp'          => $name_ftp,
            'fields_connexion'  => $fields['Connexions'],
            'fields_ftp'        => $fields['Ftps']
        );
        return $response;
    }
}
