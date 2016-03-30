<?php

namespace RMA\Bundle\DumpBundle\Ftp;

use \Exception;
use RMA\Bundle\DumpBundle\Ftp\FtpInterface;

class Rftp implements FtpInterface {
    
    protected $_path;
    protected $ftp_connect;
    protected $_repertoire_name;
    
    /**
     * Permet de lancer une connexion ftp
     * @param Array $params
     */
    public function __construct(Array $params){
        $this->ftp_connect = ftp_connect($params['ftp_ip'], $params['ftp_port'], $params['ftp_timeout']);
        $this->_path = $params['ftp_path'];
        $this->_fichier = $params['repertoire_name'] . $params['extension'];
        $this->_dir_fichier = $params['dir_fichier'];
        if ($this->ftp_connect){
            ftp_login($this->ftp_connect, $params['ftp_username'], $params['ftp_password']);
            ftp_pasv($this->ftp_connect, true);
        }
        else {
            throw new Exception("Impossible de se connecter au serveur FTP");
        }   
    }
    
    /**
     * Permet de déposer le dump sur le FTP
     * Close la connexion FTP après le transfert
     * @param type $mode
     */
    public function DepotSurFTP($mode = FTP_ASCII){
        ftp_chdir($this->ftp_connect,  $this->_path); 
        ftp_put($this->ftp_connect, $this->_fichier, $this->_dir_fichier. DIRECTORY_SEPARATOR. $this->_fichier, $mode);
        $this->CloseConnexionFTP();
    }
    
    /**
     * Permet de close la connexion courante
     */
    public function CloseConnexionFTP(){
        ftp_close($this->ftp_connect);
    }
}