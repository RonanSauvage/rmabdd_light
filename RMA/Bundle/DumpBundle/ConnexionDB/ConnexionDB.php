<?php

namespace RMA\Bundle\DumpBundle\ConnexionDB;

use Symfony\Component\Validator\Constraint as Assert;
use Doctrine\DBAL\DriverManager;

use RMA\Bundle\DumpBundle\ConnexionDB\ConnexionDBInterface;

/**
 * ConnexionDB
 *
 */
class ConnexionDB implements ConnexionDBInterface
{
    /**
     * 
     */
    private $nameConnexion;
    
    /**
     * @Assert\Type(type="integer", message="Le port doit etre un entier")
     */
    private $port;
    
     /**
     * @Assert\NotBlank()
     */
    private $username;
    
    /**
     * 
     */
    private $password;
    
    /**
     * @Assert\Ip
     */
    private $host;
    
    /**
     * Construct pour la classe ConnexionDB
     * @param array $params
     */
    public function __construct(Array $params)
    {
        $this->host = $params['host'];
        $this->port = $params['port'];
        $this->username = $params['username'];
        $this->password = $params['password'];
    }

    /**
    * Set nameConnexion
    *
    * @param string $nameConnexion
    *
    * @return ConnexionDB
    */
    public function setNameConnexion($nameConnexion)
    {
        $this->nameConnexion = $nameConnexion;

        return $this;
    }

    /**
     * Get nameConnexion
     *
     * @return string
     */
    public function getNameConnexion()
    {
        return $this->nameConnexion;
    }

    /**
     * Set port
     *
     * @param integer $port
     * @return ConnexionDB
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get port
     *
     * @return integer 
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return ConnexionDB
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return ConnexionDB
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set host
     *
     * @param string $host
     * @return ConnexionDB
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host
     *
     * @return string 
     */
    public function getHost()
    {
        return $this->host;
    }
    
    /**
     * Permet de formatter un DSN pour une ConnexionDB
     * @param string dbname 
     * @return string $dsn
     */
    public function getDSN($dbname = null)
    {
        if (is_null($dbname))
        {
            return 'mysql:host=' . $this->getHost() .';port=' . $this->getPort(); 
        }
        else {
            return 'mysql:dbname='.$dbname .';host=' . $this->getHost() .';port=' . $this->getPort(); 
        }
    }
    
    /**
     * Permet de retourner une instance \PDO pour une ConnexionDB
     * @return \PDO
     */
    public function getPDO()
    {
        $dsn = self::getDSN();
        $options = array(
             \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
         );
         return new \PDO ($dsn, $this->getUsername(), $this->getPassword(), $options);
    }
    
    /**
     * Permet de récupérer le schéma manager pour une connexionDB
     * @return SchemaManager $sm
     */
    public function getSchemaManager()
    {  
        $pdo = self::getPDO();
        
        $conn = DriverManager::getConnection(array('driver'=>'pdo_mysql', 'pdo'=>$pdo));
        $sm = $conn->getSchemaManager();
        return $sm;     
    }
    
    /**
     * Retourne toutes les databases de la connexion 
     * @return array $listDatabases
     */
    public function getListDatabases()
    {
        $schemaManager = self::getSchemaManager();
        return $schemaManager->listDatabases();
    }
    
    /**
     * Retourne les databases de la connexion sans les excludes
     * @param Array $excludes
     * @return array $databases
     */
    public function getListDatabasesWithoutExclude($excludes)
    {
        $schemaManager = self::getSchemaManager();
        $databases = $schemaManager->listDatabases();
   
        foreach ($excludes as $exclude) {
            $i = 0;
            foreach ($databases as $key => $database){           
                if($database == $exclude->getNameDatabase()){
                     unset ($databases[$key]);
                } 
            $i++;
            }   
        }
        return $databases;
    }
    
    /**
     * Permet de getter les Colonnes pour une table
     * @param string $table_name
     * @param string $database_name
     * @return boolean|array
     */
    public function getListTableColums($table_name, $database_name)
    {
        $schemaManager = self::getSchemaManager();
        $ls = $schemaManager->listTableColumns($table_name, $database_name);
        if ($ls) 
        {
            $colums = array ();
            foreach ($ls as $column) {
                array_push($colums, array (
                    "name" => $column->getName(),
                    "type" => $column->getType()
                    ));
            } 
            return $colums;
        } 
        else {
            return false;
        }
    }  
}
