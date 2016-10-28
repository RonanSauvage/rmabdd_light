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
    private $name;
    
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
     * 
     */
    private $driver;
    
    /**
     *
     * @var type array
     */
    private $excludes;
    
    /**
     * Construct pour la classe ConnexionDB
     * @param array $params
     */
    public function __construct(Array $params)
    {
        $this->host = $params['host'];
        $this->port = $params['port'];
        $this->username = $params['user'];
        $this->password = $params['password'];
        $this->driver = $params['driver'];
        $this->excludes = $params['excludes'];
    }

    /**
    * Set name
    *
    * @param string $name
    *
    * @return ConnexionDB
    */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * Set driver
     *
     * @param string $driver
     * @return ConnexionDB
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;

        return $this;
    }
    
    /**
     * Get driver
     *
     * @return string 
     */
    public function getDriver()
    {
        return $this->driver;
    }
    
    /**
     * Get xcludes
     * 
     * @return array
     */
    public function getExcludes()
    {
        return $this->excludes;
    }

    /**
     * Set excludes
     * @param array $excludes
     */
    public function setExcludes(Array $excludes)
    {
        $this->excludes = $excludes;

        return $this;
    }
    
    /**
    * Retourne les params attendus pour la connexion avec les values par défaut
    * @return array $fieldss
    */
    public static function getFields(){
        $fields = array(
            'user' => 'root',
            'password' => 'root',
            'driver' => 'pdo_mysql',
            'host' => 'localhost',
            'port' => '3306',
            'excludes' => array ('performance_schema', 'mysqld')
        );
        return $fields;
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
            return substr($this->driver, 4) .':host=' . $this->getHost() .';port=' . $this->getPort(); 
        }
        else {
            return substr($this->driver, 4) .':dbname='.$dbname .';host=' . $this->getHost() .';port=' . $this->getPort(); 
        }
    }
    
    /**
     * Permet de retourner une instance \PDO pour une ConnexionDB
     * @return \PDO
     */
    public function getPDO($database = null)
    {
        if(is_null($database)){
            $dsn = self::getDSN();
        }
        else {
            $dsn = self::getDSN($database);
        }  
        $options = array();
        if($this->driver == 'pdo_mysql'){
            $options = array(
                 \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            );
        }
        return new \PDO ($dsn, $this->getUsername(), $this->getPassword(), $options);
    }
    
    /**
     * Permet de récupérer le schéma manager pour une connexionDB
     * @return SchemaManager $sm
     */
    public function getSchemaManager()
    {  
        $pdo = self::getPDO();
        
        $conn = DriverManager::getConnection(array('driver' => $this->driver, 'pdo' => $pdo));
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
