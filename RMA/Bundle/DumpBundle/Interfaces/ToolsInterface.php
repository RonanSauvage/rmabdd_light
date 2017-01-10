<?php

namespace RMA\Bundle\DumpBundle\Interfaces;

interface ToolsInterface
{
    public static function cleanString ($string);

    public static function rrmDir($src);

    public static function formatDirWithFile($path, $fic);

    public static function getArrayDump($dump);
}
