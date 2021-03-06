<?php

namespace RMA\Bundle\DumpBundle\Dump;

use Ifsnop\Mysqldump\Mysqldump as IfsnopMysqldump;
use RMA\Bundle\DumpBundle\Interfaces\DumpInterface;
use RMA\Bundle\DumpBundle\Interfaces\ConnexionDBInterface;

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
     * @param ConnexionDBInterface $connexiondb
     * @param Array $params
     */
    public function __construct(ConnexionDBInterface $connexiondb, Array $params)
    {
        $this->_connexiondb = $connexiondb;
        $this->_mysqlDump = $this->newMysqlDump($connexiondb, $params['compress']);
        if (!file_exists($params['dir_dump'])){
            mkdir($params['dir_dump'], 0777, true);
        }             
        $this->_pathDumps = $params['dir_dump'];
        $this->_repertoire_name = $params['repertoire_name'];
        $this->_extension = $this->setExtension($params['compress']);
        $this->_params = $params;
    }
    
    /**
     * Permet d'initialiser un objet de dump
     * @param ConnexionDBInterface $connexiondb
     * @param string $compress
     * @param string $database
     * @return IfsnopMysqldump
     */
    public function newMysqlDump(ConnexionDBInterface $connexiondb, $compress, $database = null)
    {
        if(is_null($database))
        {
            $databases = $this->_connexiondb->getListDatabases();
            $database = $databases[0];
        }
        return  new IfsnopMysqldump($connexiondb->getDSN($database), 
                                        $connexiondb->getUsername(), 
                                        $connexiondb->getPassword(),
                                        array("compress" => $compress)); 
        
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
     * @param string $path
     * @return array $infos
     */
    public function execDumpForOneDatabase($name_database, $path = null)
    {
        if(is_null($path)){
            $path = $this->getPathDumpsWithDir();
        }
        if (!file_exists($path)){
            mkdir($path, 0777, true);
        }   
        $mysqlDump = $this->newMysqlDump($this->_connexiondb, $this->_params['compress'], $name_database);
        $name = $name_database . '.' . $this->_extension;
        $path_destination_interne_with_db = $path . DIRECTORY_SEPARATOR . $name;  
        $mysqlDump->start($path_destination_interne_with_db);
        $infos = array ( 
                   "$name" => $path . DIRECTORY_SEPARATOR . $name
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
     * @return string $path_dump
     */
    public function getPathDumpsWithDir() 
    {
        return $this->_pathDumps . DIRECTORY_SEPARATOR . $this->_repertoire_name;
    }
    
    /**
     * Permet de retirer les tables qui doivent être excludes de l'array d'origine
     * @param array $bases_de_donnees
     * @param array $excludes
     * @return array $bases_de_donnees
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
