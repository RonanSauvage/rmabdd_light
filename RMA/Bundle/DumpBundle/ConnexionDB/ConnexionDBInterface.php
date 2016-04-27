<?php

namespace RMA\Bundle\DumpBundle\ConnexionDB;

interface ConnexionDBInterface 
{
    public function getDSN($dbname = null);
    
    public function getPDO();

    public function getSchemaManager();

    public function getListDatabases();

    public function getListDatabasesWithoutExclude($excludes);
}
