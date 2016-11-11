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
        if(isset($params['ftp_ip']) && isset($params['ftp_path']) && isset($params['ftp_port']) && isset($params['ftp_timeout']) 
                && isset($params['repertoire_name']) && isset($params['extension']) && isset($params['dir_fic']) 
                && isset($params['ftp_username']) && isset($params['ftp_password'])){
            $this->ftp_connect = ftp_connect($params['ftp_ip'], $params['ftp_port'], $params['ftp_timeout']);
            $this->_path = $params['ftp_path'];
            $this->_fichier = $params['repertoire_name'] . $params['extension'];
            $this->_dir_fichier = $params['dir_fic'];
            $this->_username = $params['ftp_username'];
            $this->_password = $params['ftp_password'];
        }
        else {
            $default_value = self::getFields();
            $this->ftp_connect = ftp_connect($default_value['ftp_ip'], $default_value['ftp_port'], $default_value['ftp_timeout']);
            $this->_path = $default_value['ftp_path'];
            $this->_fichier = 'defaut_path';
            $this->_dir_fichier = 'file.txt';
            $this->_username = $default_value['ftp_username'];
            $this->_password = $default_value['ftp_password'];
        }
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

    /**
     * Permet de retourner les paramètres définis pour une connexion FTP
     * @return array $fields
     */
    public static function getFields()
    {
        $fields = array(
            'name_ftp'      => 'nameFTP',
            'ftp_ip'        => '127.0.0.1',
            'ftp_port'      => 21,
            'ftp_timeout'   => 90,
            'ftp_path'      => '/home/rma/dump',
            'ftp_username'  => 'ftpUsername',
            'ftp_password'  => 'ftpPassword',
        );
        
        return $fields;
    }
}
