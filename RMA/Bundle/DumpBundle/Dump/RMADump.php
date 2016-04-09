<?php

namespace RMA\Bundle\DumpBundle\Dump;

use Symfony\Component\DependencyInjection\ContainerAware;

use RMA\Bundle\DumpBundle\Ftp\FtpInterface;
use RMA\Bundle\DumpBundle\ConnexionDB\ConnexionDBInterface;
use RMA\Bundle\DumpBundle\Dump\DumpInterface;
use RMA\Bundle\DumpBundle\Zip\ZipInterface;
use Psr\Log\LoggerInterface;
use RMA\Bundle\DumpBundle\Tools\WriteDumpInterface;

/**
 * Description of RMADump
 *
 * @author rmA
 */
class RMADump extends ContainerAware {
    
    protected $_zip;
    protected $_dump;
    protected $_zip_bool;
    protected $_connexiondb;
    protected $_ftp;
    protected $_logger;
    protected $_writedump;
    
   /**
    * 
    * @param ConnexionDBInterface $connexiondb
    * @param DumpInterface $dump
    * @param ZipInterface $zip
    * @param string $zip_bool
    * @param FtpInterface $ftp
    * @param LoggerInterface $logger
    * @param WriteDumpInterface $writedump
    */
    public function __construct (ConnexionDBInterface $connexiondb, DumpInterface $dump, ZipInterface $zip, $zip_bool, FtpInterface $ftp, LoggerInterface $logger, WriteDumpInterface $writedump)
    {
        $this->_zip = $zip;
        $this->_connexiondb = $connexiondb;
        $this->_dump = $dump;  
        $this->_zip_bool = $zip_bool; 
        $this->_ftp = $ftp; 
        $this->_logger = $logger;
        $this->_writedump = $writedump;
    }
    
    /**
     * Lance un dump pour plusieurs databases
     * @param array $databases
     */
    public function rmaDumpForDatabases(Array $databases)
    {
        $infos = $this->_dump->execDumpForConnexiondb($databases);
        if ($this->_zip_bool)
        {
            $this->rmaDumpJustZip($this->_zip_bool);
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
        $infos = $this->_dump->execDumpForOneDatabase($database);
        $infos = array_merge($infos_old, $infos);
        $this->rmaLogger('Dump : La base de données '. $database .' a bien été exportée');
        return $infos;
    }
    
    /**
     * Permet de lancer l'action de zip 
     * Si le paramètre n'est pas renseigné, la valeur prise est celle initialisée dans le construct
     * Sinon si l'utilisateur peut forcer à true
     * @param boolean $zip
     */
    public function rmaDumpJustZip($zip = false)
    {
        if ($zip != false || ($this->_zip_bool && $this->_zip_bool !== 'false'))
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
        return  $this->_connexiondb->getListDatabases();
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
        $this->_logger->notice($message);
    } 
    
    /**
     * Permet d'écrire les infos liés au dump dans le fichier .dump.ini lié
     * @param array $infos
     * @param string $fichier
     */
    public function rmaWriteDump(Array $infos, $fichier  = 'C:/wamp/www/rmabdd_console/web/dump/')
    {
        $this->_writedump->writeInDumpFic($infos, $fichier);
    }
    
    /**
     * Permet d'écrire la première ligne du fichier liés au dump
     * @param string $date
     * @param string $dir_dump
     * @param string $repertoire_name
     * @param int $numer_databases
     * @param array $data
     * @return array $infos
     */
    public function rmaGetInfosDump($date, $dir_dump, $repertoire_name, $numer_databases, Array $data)
    {
        $infos = array(
            $date ." | ". $dir_dump . " | " . $repertoire_name . " | " . $numer_databases ." databases " =>  $data
        );
        return $infos;
    }
}

