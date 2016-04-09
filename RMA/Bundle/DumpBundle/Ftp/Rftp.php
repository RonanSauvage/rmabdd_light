<?php

namespace RMA\Bundle\DumpBundle\Ftp;

use \Exception;
use RMA\Bundle\DumpBundle\Ftp\FtpInterface;

class Rftp implements FtpInterface {
    
    protected $_path;
    protected $ftp_connect;
    protected $_repertoire_name;
    protected $_username;
    protected $_password;
    
    /**
     * Permet de lancer une connexion ftp
     * @param Array $params
     */
    public function __construct(Array $params){
        $this->ftp_connect = ftp_connect($params['ftp_ip'], $params['ftp_port'], $params['ftp_timeout']);
        $this->_path = $params['ftp_path'];
        $this->_fichier = $params['repertoire_name'] . $params['extension'];
        $this->_dir_fichier = $params['dir_fichier'];
        $this->_username = $params['ftp_username'];
        $this->_password = $params['ftp_password'];
    }
    
    /**
     * Permet de déposer le dump sur le FTP
     * Close la connexion FTP après le transfert
     * @param type $mode
     */
    public function depotSurFTP($mode = FTP_ASCII){
        if ($this->ftp_connect){
            ftp_login($this->ftp_connect, $this->_username, $this->_password);
            ftp_pasv($this->ftp_connect, true);
        }
        else {
            throw new Exception("Impossible de se connecter au serveur FTP");
        } 
        ftp_chdir($this->ftp_connect,  $this->_path); 
        ftp_put($this->ftp_connect, $this->_fichier, $this->_dir_fichier. DIRECTORY_SEPARATOR. $this->_fichier, $mode);
        $this->CloseConnexionFTP();
    }
    
    /**
     * Permet de close la connexion courante
     */
    public function closeConnexionFTP(){
        ftp_close($this->ftp_connect);
    }
}
