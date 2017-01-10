<?php

namespace RMA\Bundle\DumpBundle\Interfaces;

use RMA\Bundle\DumpBundle\Interfaces\ConnexionDBInterface;

interface ExportManagerInterface 
{
    public function createDatabaseWithSqlFic ($fic_script_sql, $database_name, ConnexionDBInterface $connexionDB);

    public function lauchScriptForMigration($script_migration, $database_name, ConnexionDBInterface $connexionDB);
}
