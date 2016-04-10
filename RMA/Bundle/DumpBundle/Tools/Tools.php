<?php

namespace RMA\Bundle\DumpBundle\Tools;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Description of Tools
 *
 * @author rma
 */
class Tools extends ContainerAware implements ToolsInterface{
    
    protected $_container;
    
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
    public static function rrmdir($src) 
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
     * Permet d'hydrater l'array Params selon les options définies au niveau de la commande
     * @param InputInterface $input
     * @return array $params
     */

    public function hydrateInputOptions (InputInterface $input, ContainerInterface $container)
    {
        $rOptions = $input->getOptions();
        $params = array ();
        $params['repertoire_name'] = date('Y-m-d-H\\hi') . '__' . uniqid();
        $params['logger'] = $container->get('logger');
        foreach ($rOptions as $rOption => $rvalue)
        {
            if($container->hasParameter('rma_'.$rOption))
            {
                $$rOption = $container->getParameter('rma_'.$rOption);
        }
            if (!is_null($rvalue))
            {
                $$rOption = $rvalue;
            }
            $params[$rOption] = $$rOption;
        }
        return $params;
    }

}

