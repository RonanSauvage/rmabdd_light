<?php

namespace RMA\Bundle\DumpBundle\Tools;

use RMA\Bundle\DumpBundle\Interfaces\ToolsInterface;

/**
 * Description of Tools
 *
 * @author rma
 */
class Tools implements ToolsInterface{

    /**
     * Permet d'obtenir le path complet du fichier en vérifiant le dernier caractère 
     * @param string $dir
     * @param string $fichier
     */
    public static function formatDirWithFile($path, $fichier){
       // On vérifie si l'utilisateur a saisi un slash de fin du chemin
        if (substr($path, -1) != "/" && substr($path, -1) != "\\" )
        {
            $path .= '/';
        }
        return $path . $fichier;
    }
    
    /**
     * Returne une array avec les différentes données organisées
     * @param string $dump
     * @return array $resultat
     */
    public static function getArrayDump($dump)
    {
        $resultats = explode('|', $dump);
        $resultat = array (
            "date"          =>  trim($resultats[0]),
            "path"          =>  trim($resultats[1]),
            "identifiant"   =>  trim($resultats[2]),
            "number_db"     =>  trim($resultats[3])
        );
        return $resultat;
    }
    
    /**
     * Permet de supprimer le contenu d'un dossier ainsi que le dossier lui même
     * @param string $src
     */
    public static function rrMdir($src)
    {
        if($dir = opendir($src)) {
            while(false !== ( $file = readdir($dir)) ) 
            {
                if (( $file != '.' ) && ( $file != '..' )) 
                {
                    $full = $src . '/' . $file;
                    if (is_dir($full)) 
                    {
                        self::rrmdir($full);
                    }
                    else 
                    {
                        unlink($full);
                    }
                }
            }
            closedir($dir);
            rmdir($src);
        }
        else {
            throw new \Exception('Le répertoire ' . $src . ' est introuvable.');
        }
    }
    
    /**
     * Permet de nettoyer une chaine des caractères spéciaux
     * @param string $chaine
     * @param boolean $acceptUnderscore
     * @return string
     */
    public static function cleanString($chaine, $acceptUnderscore = false)
    {
        $caracteres = array(
            'a', 'Á' => 'a', 'Â' => 'a', 'Ä' => 'a', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', '@' => 'a',
            'È' => 'e', 'É' => 'e', 'Ê' => 'e', 'Ë' => 'e', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', '€' => 'e',
            'Ì' => 'i', 'Í' => 'i', 'Î' => 'i', 'Ï' => 'i', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Ö' => 'o', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o',
            'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'µ' => 'u',
            'Œ' => 'oe', 'œ' => 'oe',
            '$' => 's'
        );

        $chaine = strtr($chaine, $caracteres);
        if(!$acceptUnderscore){
            $chaine = preg_replace('#[^A-Za-z0-9]+#', '-', $chaine);
        }
        return trim($chaine, '-');
    }

    
    /**
     * Permet de scanner un répertoire
     * @param string $Directory
     * @return array $r
     * @throws \Exception
     */
    public function scanDirectory($Directory, array $excludes = array())
    {
        $ritit = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($Directory), \RecursiveIteratorIterator::CHILD_FIRST); 
        $r = array(); 
        foreach ($ritit as $splFileInfo) { 
           if($splFileInfo->getFilename() == '.' || $splFileInfo->getFilename() == '..'){
               continue;
           }
           $path = $splFileInfo->isDir() 
                 ? array($splFileInfo->getFilename() => array()) 
                 : array($splFileInfo->getFilename()); 

           for ($depth = $ritit->getDepth() - 1; $depth >= 0; $depth--) { 
               $path = array($ritit->getSubIterator($depth)->current()->getFilename() => $path); 
           } 
           $r = array_merge_recursive($r, $path); 
        } 
        
        return $r;
    }  
    
    /**
     * Permet d'éliminer les répertoires . et .. du listing ainsi que les valeurs à exclure envoyées en param
     * @param string $dir
     * @param array $excludes
     * @return array $dirs
     */
    public static function removeFalseDir($dir, Array $excludes)
    {
        $dirs = scandir($dir);
        $dirs = array_diff($dirs, array('..', '.'));
        $dirs = array_diff($dirs, $excludes);
        return array_values($dirs);
    }
}
