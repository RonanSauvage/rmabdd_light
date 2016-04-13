<?php

namespace RMA\Bundle\DumpBundle\Tools;

use RMA\Bundle\DumpBundle\Tools\WriteDumpInterface;
 
/**
 * Description of WriteDump
 *
 * @author rmA
 */
class WriteDump implements WriteDumpInterface {
    
    CONST NAME_DUMP = ".dump.ini";
    
    /**
     * Permet d'écrire une array dans un fichier fourni en paramètre
     * @param array $infos
     * @param string $path_dir
     */
    public function writeInDumpFic(Array $infos, $path_dir)
    {
        $path_file = Tools::formatDirWithDumpFile($path_dir, self::NAME_DUMP);
        $content = $this->recupData($path_file);
        $file_content = array_merge($content, $infos);
        $this->putInitFile($path_file, $file_content);
    }
    
    /**
     * Permet d'écrire une array dans un fichier de type .ini
     * @param string $file
     * @param array $array
     * @param int $i
     */
    private function putInitFile($file, $array, $i = 0){
       $res = array();
        foreach($array as $key => $val)
        {
            if(is_array($val))
            {
                $res[] = "[$key]";
                foreach($val as $skey => $sval) $res[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.$sval.'"');
            }
            else $res[] = "$key = ".(is_numeric($val) ? $val : '"'.$val.'"');
        }
        $this->safeFileRewrite($file, implode("\r\n", $res));
    }
    
    /**
     * Ecris les données dans une array
     * @param striing $fileName
     * @param array $dataToSave
     */
    private function safeFileRewrite($fileName, $dataToSave)
    {   
        if ($fp = fopen($fileName, 'w'))
        {
            $startTime = microtime();
            do
            {            
                $canWrite = flock($fp, LOCK_EX);
                if(!$canWrite) usleep(round(rand(0, 100)*1000));
            } while ((!$canWrite)and((microtime()-$startTime) < 1000));
            if ($canWrite)
            {            
                fwrite($fp, $dataToSave);
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }
    }
    
    /**
     * Permet d'initialiser une array avec les données dans un fichier
     * Ou vide si le fichier n'existe pas
     * @param string $file
     * @return array $content
     */
    private function recupData($file)
    {
        $content = array();
        // On vérifie que le fichier existe
        if (file_exists($file)){
            $content = parse_ini_file($file, true);
        }  
        return $content;
    }
    
    /**
     * Permet de remplacer les DATAS d'un fichier de dump 
     * @param array $infos
     * @param string $path_dir
     */
    public function remplaceDumpFic(Array $infos, $path_dir)
    {
        $path_file = Tools::formatDirWithDumpFile($path_dir, self::NAME_DUMP);
        $this->putInitFile($path_file, $infos);
    }
    
}
