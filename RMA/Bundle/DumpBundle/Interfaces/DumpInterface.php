<?php

namespace RMA\Bundle\DumpBundle\Interfaces;

interface DumpInterface 
{
    public function execDumpForConnexiondb (Array $databases);
    
    public function execDumpForOneDatabase($name_database);
    
    public function getPathDumpsWithDir();
        
    public function getPathDumps();
}
