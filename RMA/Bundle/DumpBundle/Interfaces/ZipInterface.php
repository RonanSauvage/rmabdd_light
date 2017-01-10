<?php

namespace RMA\Bundle\DumpBundle\Interfaces;

interface ZipInterface 
{
    public static function folderToZip($folder, &$zipFile, $exclusiveLength);

    public function execZip($sourcePath) ;
    
    public function getPathZip();
}
