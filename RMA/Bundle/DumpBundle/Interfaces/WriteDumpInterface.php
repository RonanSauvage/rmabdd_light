<?php

namespace RMA\Bundle\DumpBundle\Interfaces;

interface WriteDumpInterface 
{
    public function writeInDumpFic (Array $infos, $file);
}
