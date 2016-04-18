<?php

namespace RMA\Bundle\DumpBundle\Dump;

use Ifsnop\Mysqldump\Mysqldump as IfsnopMysqldump;
use RMA\Bundle\DumpBundle\ConnexionDB\ConnexionDB;
use RMA\Bundle\DumpBundle\Dump\DumpInterface;

/**
 * Description of DumpMysql
 *
 * @author rmA
 */
class DumpMysql implements DumpInterface{
    
    protected $_mysqlDump;
    protected $_pathDumps;
    protected $_extension;
    protected $_repertoire_name;
    protected $_connexiondb;
    
    /**
     * Constructeur de l'object MySQLDump
     * @param ConnexionDB $connexiondb
     * @param Array $params
     */
    public function __construct(ConnexionDB $connexiondb, $params)
    {
        $this->_connexiondb = $connexiondb;
        $databases = $this->_connexiondb->getListDatabases();
        $this->_mysqlDump = new IfsnopMysqldump($connexiondb->getDSN($databases[0]), 
                                        $connexiondb->getUsername(), 
                                        $connexiondb->getPassword(),
                                        array("compress" => $params['compress'])); 
        
        if (!file_exists($params['dir_dump'])){
            mkdir($params['dir_dump']);
        }      
        $this->_pathDumps = $params['dir_dump'];
        $this->_repertoire_name = $params['repertoire_name'];
        $this->_extension = $this->setExtension($params['compress']);
    }
    
    /**
     * Permet de lancer le dump pour une liste de databases
     * @param array $databases
     * @param array $excludes
     */
    public function execDumpForConnexiondb (Array $databases)
    {
        $infos = array();
        foreach ($databases as $database){
           $infos = array_merge($infos, $this->execDumpForOneDatabase($database));         
        }
        return $infos;
    }
       
    /**
     * Permet d'executer un dump pour une base de données précise
     * @param string $name_database
     * @return array $infos
     */
    public function execDumpForOneDatabase($name_database)
    {
        if (!file_exists($this->getPathDumpsWithDir())){
            mkdir($this->getPathDumpsWithDir());
        }   
        $name = $name_database . '.' . $this->_extension;
        $path_destination_interne_with_db = $this->getPathDumpsWithDir() . DIRECTORY_SEPARATOR . $name;  
        $this->_mysqlDump->start($path_destination_interne_with_db);
        $infos = array ( 
                   "$name" => $this->getPathDumpsWithDir() . DIRECTORY_SEPARATOR . $name
            );
        return $infos;
    }
    
    /**
     * Détermine l'extension des fichiers selon la compression
     * @param string $compression
     * @return string extension
     */
    public function setExtension($compression) 
    {
        if ($compression == "none"){
            return "sql";
         }
         elseif ($compression == "gzip"){
            return "gz";
         }
         else {
            return "bz2";
         }
    }
    
    /**
     * Getter de l'extension
     * @return string _extension
     */
    public function getExtension()
    {
        return $this->_extension;
    }
        
    /**
     * Getter du path du répertoire Dump
     * @return string _pathDumps
     */
    public function getPathDumps()
    {
        return $this->_pathDumps;
    }
    
    /**
     * Retourne le chemin d'accès pour la création au répertoire directement
     * @return string
     */
    public function getPathDumpsWithDir() 
    {
        return $this->_pathDumps . DIRECTORY_SEPARATOR . $this->_repertoire_name;
    }
    
    /**
     * Permet de retirer les tables qui doivent être excludes de l'array d'origine
     * @param type array $bases_de_donnees
     * @param type array $excludes
     * @return type array $bases_de_donnees
     */
    public function unsetDataTablesExclude($bases_de_donnees, $excludes){      
        foreach ($excludes as $exclude) {
            $i = 0;
            foreach ($bases_de_donnees as $key => $base_de_donnees){           
                if($base_de_donnees == $exclude){
                     unset ($bases_de_donnees[$key]);
                } 
            $i++;
            }   
        }
        return $bases_de_donnees;
    }

}
