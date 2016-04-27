<?php

namespace RMA\Bundle\DumpBundle\Tools;


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
    public static function formatDirWithDumpFile($path, $fichier){
       // On vérifie si l'utilisateur a saisi un slash de fin du chemin
        if (substr($path, -1) != "/" && substr($path, -1) != "\\" )
        {
            $path .= DIRECTORY_SEPARATOR;
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
        $dir = opendir($src);
        while(false !== ( $file = readdir($dir)) ) 
        {
            if (( $file != '.' ) && ( $file != '..' )) 
            {
                $full = $src . '/' . $file;
                if ( is_dir($full) ) 
                {
                    rrmdir($full);
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
    
    /**
     * Permet de nettoyer une chaine des caractères spéciaux
     * @param string $chaine
     * @return string
     */
    public static function cleanString($chaine)
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
        $chaine = preg_replace('#[^A-Za-z0-9]+#', '-', $chaine);
        return trim($chaine, '-');
    }

    
    /**
     * Permet de scanner un répertoire
     * @param string $Directory
     * @throws \Exception
     */
    public static function scanDirectory($Directory)
    {
        if (opendir($Directory))
        {
            $MyDirectory = opendir($Directory);
        }
        else 
        {
            throw new \Exception("Impossible d'ouvrir le répertoire " . $Directory);
        }
        $myarray = array();
        while($Entry = readdir($MyDirectory)) {
            if(is_dir($Directory.'/'.$Entry)&& $Entry != '.' && $Entry != '..') {
                $myarray[$Entry] =  array();
                self::scanDirectory($Directory.'/'.$Entry);
            }
            else {
                array_push($myarray[$Entry], $Entry);
            }
        }
        closedir($MyDirectory);
        return $myarray;
    }  
}

