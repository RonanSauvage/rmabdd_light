<?php

namespace RMA\Bundle\DumpBundle\Tools;


interface ToolsInterface
{
    public static function cleanString ($string);

    public static function rrmDir($src);

    public static function formatDirWithDumpFile($path, $fic);

    public static function getArrayDump($dump);
}
