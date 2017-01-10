<?php

namespace RMA\Bundle\DumpBundle\Tools;

use RMA\Bundle\DumpBundle\Interfaces\ConnexionDBInterface;
use Psr\Log\LoggerInterface;

class DatabaseManager {
    
    protected $logger;
    
    public function __construct(LoggerInterface $logger){
        $this->logger = $logger;
    }
    
    /**
     * Permet de créer une base de données vide avec le nom envoyé en paramètre
     * @param ConnexionDBInterface $connexionDB
     * @param string $database
     */
    public function createOneDatabase(ConnexionDBInterface $connexionDB, $database)
    {
        $pdo = $connexionDB->getPDO();
        try {
            $pdo->exec('CREATE DATABASE ' . $database);
        } catch (\Exception $ex) {
            throw new \Exception('Création de la base de données : ' . $database . ' impossible. Message d\'erreur : ' . $ex->getMessage());
        }
    }
    
    /**
     * Permet de delete la database passée en paramètre
     * @param string $database
     */
    public function deleteOneDatabase(ConnexionDBInterface $connexionDB, $database){
        $pdo = $connexionDB->getPDO();
        try {
            $pdo->exec('DROP DATABASE IF EXISTS ' . $database);
        } catch (\Exception $ex) {
            throw new \Exception('Suppression de la base de données : ' . $database . ' impossible. Message d\'erreur : ' . $ex->getMessage());
        }
    }
}
