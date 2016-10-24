<?php

namespace RMA\Bundle\DumpBundle\Tools;

interface ExportDatabaseInterface 
{
    public function createDatabaseWithSqlFic ($fic_script_sql, $database_name);
    
    public function initDB ($database_name);
    
    public function deleteDB ($database_name);
    
    public function lauchScriptForMigration($script_migration, $database_name);
}
