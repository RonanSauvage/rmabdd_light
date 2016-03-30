<?php

namespace RMA\Bundle\DumpBundle\Dump;

use Symfony\Component\DependencyInjection\ContainerAware;

use RMA\Bundle\DumpBundle\Ftp\FtpInterface;
use RMA\Bundle\DumpBundle\ConnexionDB\ConnexionDBInterface;
use RMA\Bundle\DumpBundle\Dump\DumpInterface;
use RMA\Bundle\DumpBundle\Zip\ZipInterface;

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
    
   /**
    * 
    * @param ConnexionDBInterface $connexiondb
    * @param DumpInterface $dump
    * @param ZipInterface $zip
    * @param string $zip_bool
    * @param FtpInterface $ftp
    */
    public function __construct (ConnexionDBInterface $connexiondb, DumpInterface $dump, ZipInterface $zip, $zip_bool, FtpInterface $ftp)
    {
        $this->_zip = $zip;
        $this->_connexiondb = $connexiondb;
        $this->_dump = $dump;  
        $this->_zip_bool = $zip_bool; 
        $this->_ftp = $ftp; 
    }
    
    /**
     * Lance un dump pour plusieurs databases
     * @param array $databases
     */
    public function rmaDumpForDatabases(Array $databases)
    {
        $this->_dump->execDumpForConnexiondb($databases);
        
        if ($this->_zip_bool)
        {
            $this->_zip->execZip($this->_dump->getPathDumpsWithDir());
        }
    }
    
    /**
     * Lance un dump pour une database
     * @param string $database
     */
    public function rmaDumpForDatabase($database)
    {
        $this->_dump->execDumpForOneDatabase($database);
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
        $this->_ftp->DepotSurFTP();
    }
}

