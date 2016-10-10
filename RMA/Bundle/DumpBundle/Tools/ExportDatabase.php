<?php

namespace RMA\Bundle\DumpBundle\Tools;

use RMA\Bundle\DumpBundle\ConnexionDB\ConnexionDBInterface;

/**
 * Description of ExportDatabase
 *
 * @author rmA
 */
class ExportDatabase {
    
    protected $_connexiondb;
    protected $_params;
    
    public function __construct(ConnexionDBInterface $connexiondb, Array $params)
    {
        $this->_connexiondb = $connexiondb;
        
        $this->_params = $params;
    }
    
    /**
     * Permet de créer une base de données et de l'initialiser avec un fichier sql
     * @param string $fic_script_sql
     * @param string $database
     * @throws \Exception
     */
    public function createDatabaseWithSqlFic($fic_script_sql, $database)
    {
        if($this->initDB($database)){
            $pdo = $this->_connexiondb->getPDO($database);
            $this->launchScriptSQL($fic_script_sql, $pdo);
        }
        else {
            $error = 'Erreur lors de la création de la base de données temporaire de traitement';
            $this->_params['logger']->notice($error);
            throw new \Exception ($error);
        }
    }
    
    /**
     * Permet de créer une base de données à partir de la connexion PDO avec le nom transmis en param
     * @param string $database
     * @return int | false
     */
    public function initDB($database){
        $pdo = $this->_connexiondb->getPDO();
        return $pdo->exec('CREATE DATABASE ' . $database);
    }
    
    /**
     * Permet de delete la database passée en paramètre
     * @param type $database
     */
    public function deleteDB($database){
        $pdo = $this->_connexiondb->getPDO();
        return $pdo->exec('DROP DATABASE IF EXISTS ' . $database);
    }
    
    /**
     * 
     * @param string $script_migration
     * @param string $database
     */
    public function lauchScriptForMigration($script_migration, $database){
        $pdo = $this->_connexiondb->getPDO($database);
        $this->launchScriptSQL($script_migration, $pdo);
    }
    
    /**
     * Permet de lancer un script SQL sur une base de données envoyée en param
     * @param string $fic_script_sql
     * @param \PDO $pdo
     * @throws \Exception
     */
    private function launchScriptSQL($fic_script_sql, \PDO $pdo){
        $op_data = '';
        $lines = file($fic_script_sql);
        foreach ($lines as $line)
        {
            if (substr($line, 0, 2) == '--' || $line == '')//This IF Remove Comment Inside SQL FILE
            {
                continue;
            }
            $op_data .= $line;
            if (substr(trim($line), -1, 1) == ';')//Breack Line Upto ';' NEW QUERY
            {
                // On exécute l'import 
                try {
                    $pdo->query($op_data);
                    $this->_params['logger']->notice('Requête importée : ' . $op_data);
                    $op_data = '';
                } catch (\Exception $ex) {
                    throw new \Exception ($ex);
                }  
            }
        }
    }
}
