<?php

namespace RMA\Bundle\DumpBundle\Tools;

use RMA\Bundle\DumpBundle\Interfaces\ConnexionDBInterface;
use Psr\Log\LoggerInterface;
use RMA\Bundle\DumpBundle\Interfaces\ExportManagerInterface;

/**
 * Description of ExportManager
 * Permet de manipuler la base de données pour lancer un script SQL sur une DB
 * @author rmA
 */
class ExportManager implements ExportManagerInterface {
    
    protected $databaseManager;
    protected $logger;
    
    public function __construct(LoggerInterface $logger, DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
        $this->logger = $logger;
    }
    
    /**
     * Permet de créer une base de données et de l'initialiser avec un fichier sql
     * @param string $fic_script_sql
     * @param string $database
     * @param ConnexionDBInterface $connexionDB
     */
    public function createDatabaseWithSqlFic($fic_script_sql, $database, ConnexionDBInterface $connexionDB)
    {
        $this->databaseManager->createOneDatabase($connexionDB, $database);
        $pdo = $connexionDB->getPDO($database);
        $this->launchScriptSQL($fic_script_sql, $pdo); 
    }
    
    /**
     * 
     * @param string $script_migration
     * @param string $database
     * @param ConnexionDBInterface $connexionDB
     */
    public function lauchScriptForMigration($script_migration, $database, ConnexionDBInterface $connexionDB){
        $pdo = $connexionDB->getPDO($database);
        $this->launchScriptSQL($script_migration, $pdo);
    }
    
    /**
     * Permet de lancer un script SQL sur une base de données envoyée en param
     * @param string $fic_script_sql
     * @param \PDO $pdo
     * @throws \Exception
     */
    public function launchScriptSQL($fic_script_sql, \PDO $pdo){
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
                    $this->logger->notice('Requête importée : ' . $op_data);
                    $op_data = '';
                } catch (\Exception $ex) {
                    $this->logger->notice('Error lors de la requête : ' . $ex->getMessage());
                    throw new \Exception ($ex);
                }  
            }
        }
    }
}
