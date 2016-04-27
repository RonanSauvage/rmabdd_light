<?php

namespace RMA\Bundle\DumpBundle\Dump;

use RMA\Bundle\DumpBundle\Ftp\FtpInterface;
use RMA\Bundle\DumpBundle\ConnexionDB\ConnexionDBInterface;
use RMA\Bundle\DumpBundle\Dump\DumpInterface;
use RMA\Bundle\DumpBundle\Zip\ZipInterface;
use RMA\Bundle\DumpBundle\Tools\WriteDumpInterface;

/**
 * Description of RMADump
 *
 * @author rmA
 */
class RMADump {
    
    protected $_zip;
    protected $_dump;
    protected $_connexiondb;
    protected $_ftp;
    protected $_writedump;
    protected $_params;
    
   /**
    * 
    * @param ConnexionDBInterface $connexiondb
    * @param DumpInterface $dump
    * @param ZipInterface $_zip_bool
    * @param FtpInterface $ftp
    * @param WriteDumpInterface $writedump
    * @param Array $params | ['zip'], ['logger'], ['excludes'], ['date'], ['repertoire_name'], ['dir_dump'] 
    */
    public function __construct (ConnexionDBInterface $connexiondb, DumpInterface $dump, ZipInterface $zip, FtpInterface $ftp, WriteDumpInterface $writedump, Array $params)
    {
        $this->_zip = $zip;
        $this->_connexiondb = $connexiondb;
        $this->_dump = $dump;  
        $this->_ftp = $ftp; 
        $this->_writedump = $writedump;
        $this->_params = $params;
    }
    
    /**
     * Lance un dump pour plusieurs databases
     * @param array $databases
     */
    public function rmaDumpForDatabases(Array $databases)
    {
        $infos = $this->_dump->execDumpForConnexiondb($databases);
        if ($this->_params['zip'] == 'yes')
        {
            $this->rmaDumpJustZip($this->_params['zip']);
        }
        return $infos;
    }
    
    /**
     * Lance un dump pour une database
     * @param string $database
     * @param array $infos
     */
    public function rmaDumpForDatabase($database, $infos_old)
    {
        $infos = $this->_dump->execDumpForOneDatabase($database, $this->_params['excludes']);
        $infos = array_merge($infos_old, $infos);
        $this->rmaLogger('Dump : La base de données '. $database .' a bien été exportée');
        return $infos;
    }
    
    /**
     * Permet de lancer l'action de zip 
     * Si le paramètre n'est pas renseigné, la valeur prise est celle initialisée dans le construct
     * @param boolean $zip
     */
    public function rmaDumpJustZip($zip = 'no')
    {
        if ($zip !== 'no' || ($this->_params['zip'] !== 'no'))
        {
            $this->_zip->execZip($this->_dump->getPathDumpsWithDir());
            $this->rmaLogger('ZIP : L\'archive zip a été correctement créée');
        }
    }  
    
    /**
     * Permet de récupérer les databases dans une base de données
     * @return Array $databases
     */
    public function rmaDumpGetListDatabases()
    {
        $databases = $this->_connexiondb->getListDatabases();
        return $this->_dump->unsetDataTablesExclude($databases, $this->_params['excludes']);
    }
    
    /**
     * Permet de lancer un FTP 
     */
    public function rmaDepotFTP()
    {
        $this->_ftp->depotSurFTP();
        $this->rmaLogger('FTP : L\'export FTP s\'est correctement déroulé');
    }
    
    /**
     * Permet d'enregistrer des logs
     * @param string $message
     */
    public function rmaLogger($message)
    {
        $this->_params['logger']->notice($message);
    } 
    
    /**
     * Permet d'écrire les infos liées au dump dans le fichier .dump.ini lié
     * @param array $infos
     */
    public function rmaWriteDump(Array $infos)
    {
        $this->_writedump->writeInDumpFic($infos, $this->_params['dir_dump']);
    }
    
    /**
     * Permet d'écrire la première ligne du fichier liés au dump
     * @param int $numer_databases
     * @param array $data
     * @return array $infos
     */
    public function rmaGetInfosDump($numer_databases, $data)
    {
        $infos = array(
            $this->_params['date'] ." | ".  $this->_params['dir_dump'] . " | " .  $this->_params['repertoire_name'] . " | " . $numer_databases ." databases " =>  $data
        );
        return $infos;
    }
    
    /**
    * Permet de lancer un dump et d'enregistrer les logs correspondants
    * @param array $databases
    *
    */
    public function dumpAndWriteLogs(Array $databases)
    {
        $logs = array();
        foreach ($databases as $database)
        {
            $logs = $this->rmaDumpForDatabase($database, $logs); 
        }
        $infos = $this->rmaGetInfosDump(count($databases), $logs);
        $this->rmaWriteDump($infos, $this->_params['dir_dump']);
    }
    
    /**
     * Méthode pour modifier un paramètre à n'importe quel moment (après initialisation notamment)
     * return l'objet mis à jour 
     */
    public function setParams($params_name, $value)
    {
        $this->_params[$params_name] = $value;
        return $this;
    }
}

