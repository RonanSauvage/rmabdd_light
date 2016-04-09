<?php

namespace RMA\Bundle\DumpBundle\Tools;

interface WriteDumpInterface 
{
    public function writeInDumpFic (Array $infos, $file);
}
