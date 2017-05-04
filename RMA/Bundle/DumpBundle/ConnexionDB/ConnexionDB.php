<?php

namespace RMA\Bundle\DumpBundle\ConnexionDB;

use Symfony\Component\Validator\Constraint as Assert;
use Doctrine\DBAL\DriverManager;

use RMA\Bundle\DumpBundle\Interfaces\ConnexionDBInterface;

/**
 * ConnexionDB
 *
 */
class ConnexionDB implements ConnexionDBInterface
{
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
     * @var string
     */
    private $driver;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $excludes;
    
    /**
     * Construct pour la classe ConnexionDB
     * @param array $params
     */
    public function __construct(Array $params)
    {
        $paramsDB = $params['connexion_db'];
        $this->host = $paramsDB['host'];
        $this->port = $paramsDB['port'];
        $this->username = $paramsDB['user'];
        $this->password = $paramsDB['password'];
        $this->driver = $paramsDB['driver'];
        $this->excludes = $paramsDB['excludes'];
    }
    
    public function getUsername(){
        return $this->username;
    }
    
    public function getPassword(){
        return $this->password;
    }

    /**
     * @param string $name
     */
    public function setName($name){
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(){
        return $this->name;
    }

    /**
     * @return string
     */
    public function getHost(){
        return $this->host;
    }

    /**
     * @return string
     */
    public function getDriver(){
        return $this->driver;
    }

    /**
     * @return string
     */
    public function getPort(){
        return $this->port;
    }

    /**
     * @return array
     */
    public function getExcludes(){
        return $this->excludes;
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
            'excludes' => array ('performance_schema', 'mysqld', 'mysql')
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
            return substr($this->driver, 4) .':host=' . $this->host .';port=' . $this->port; 
        }
        else {
            return substr($this->driver, 4) .':dbname='.$dbname .';host=' . $this->host .';port=' . $this->port; 
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
        return new \PDO ($dsn, $this->username, $this->password, $options);
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
