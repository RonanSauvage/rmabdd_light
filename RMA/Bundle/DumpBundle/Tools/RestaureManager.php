<?php

namespace RMA\Bundle\DumpBundle\Tools;

use RMA\Bundle\DumpBundle\Interfaces\ConnexionDBInterface;
use Psr\Log\LoggerInterface;
use RMA\Bundle\DumpBundle\Tools\DatabaseManager;
use RMA\Bundle\DumpBundle\Interfaces\ExportManagerInterface;

class RestaureManager {
    
    protected $logger;
    
    protected $databaseManager;
    
    protected $exportManager;
    
    /**
     * 
     * @param LoggerInterface $logger
     * @param DatabaseManager $databaseManager
     * @param ExportManagerInterface $exportManager
     */
    public function __construct(LoggerInterface $logger, DatabaseManager $databaseManager, ExportManagerInterface $exportManager)
    {
        $this->logger = $logger;
        $this->databaseManager = $databaseManager;
        $this->exportManager = $exportManager;
    }
    
    /**
     * Permet de restaurer une base de données à partir d'un script SQL 
     * database : correspond au nom de la base de données souhaité
     * scriptSQL : désigne le path pour exécuter le script 
     * @param ConnexionDBInterface $connexionDB
     * @param string $database
     * @param string $scriptSQL
     */
    public function restaureOneDatabase(ConnexionDBInterface $connexionDB, $database, $scriptSQL){
        $this->databaseManager->createOneDatabase($connexionDB, $database);
        $this->exportManager->lauchScriptForMigration($scriptSQL, $database, $connexionDB);
    } 
}
