<?php

namespace RMA\Bundle\DumpBundle\Tools;

use RMA\Bundle\DumpBundle\Tools\WriteDump;
use RMA\Bundle\DumpBundle\Tools\SyncDumpInterface;

/**
 * Description of SyncDump
 *
 * @author rmA
 */
class SyncDump implements SyncDumpInterface {
    
    CONST NAME_DUMP = ".dump.ini";
    
    /**
     * Permet d'écrire une array dans un fichier fourni en paramètre
     * @param array $infos
     * @param string $file
     */
    public function syncRep($dir_rep)
    {       
        // On récupère les données stockées dans .dump.init
        $content = $this->recupData($dir_rep);  
       
        // On récupère tous les répertoires dans le répertoire envoyé en paramètre
        $contenu_content_rep = array_diff(scandir($dir_rep), array('..', '.', self::NAME_DUMP));
        $contenu_content_rep = array_values($contenu_content_rep);
        $my_array = array ();
                 
        foreach ($content as $name_dump => $data_dump)
        {
            $resultats = explode('|', $name_dump);
            foreach ($contenu_content_rep as $dump_in_dir)
            {
                if ($dump_in_dir == trim($resultats[2])){
                    array_push($my_array, trim($resultats[2]));
                }
            }          
            
        }
        $file_content = array_merge($content, $infos);
        $this->putInitFile($file, $file_content);
    }
    
    
    /**
     * Permet d'initialiser une array avec les données dans un fichier
     * Ou vide si le fichier n'existe pas
     * @param string $dir_rep
     * @return array $content
     */
    private function recupData($dir_rep)
    {
        $fic = Tools::formatDirWithDumpFile($dir_rep, self::NAME_DUMP);
        $content = array();
        // On vérifie que le fichier existe
        if (file_exists($fic)){
            $content = parse_ini_file($fic, true);
        }  
        return $content;
    }

    /**
     * Permet de scanner un répertoire
     * @param string $Directory
     * @throws \Exception
     */
    function scanDirectory($Directory)
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
            if(is_dir($Directory.'/'.$Entry)&& $Entry != '.' && $Entry != '..' && $Entry != self::NAME_DUMP) {
                $myarray[$Entry] =  array();
                $this->scanDirectory($Directory.'/'.$Entry);
            }
            else {
                array_push($myarray[$Entry], $Entry);
            }
        }
        closedir($MyDirectory);
    }
    
    /**
     * Permet de supprimer les dumps plus anciens que le nombre de jours envoyé
     * @param string $dir_rep
     * @param int $jour
     */
    public function deleteOldDump($dir_rep, $jour)
    {
        $date = new \Datetime();
        $date->sub(new \DateInterval('P'.$jour.'D'));
        return $this->selectDumpWithDate($dir_rep, $date);
    }
    
    /**
     * Permet de sélectionner les dump plus anciens que la date sélectionnée
     * Suppression du dump 
     * @param string $dir_rep
     * @param \Datetime $date
     */
    public function selectDumpWithDate($dir_rep, $date)
    {
        $content = $this->recupData($dir_rep); 
        $a = 0;
        foreach ($content as $dump => $data)
        {
            $datas = Tools::getArrayDump($dump);
            $date_dump = new \Datetime(substr($datas['date'], 0, 10));
            if ($date_dump < $date)
            {
                $path_dump_to_delete = Tools::formatDirWithDumpFile($dir_rep , $datas['identifiant']);
                // On supprime le dump physiquement
                // On vérifie que le dossier physique existe
                if (is_dir($path_dump_to_delete))
                {
                    Tools::rrmdir($path_dump_to_delete);
                    $a += 1;
                }

                // On met à jour le fichier de dump
                unset($content[$dump]);
            }
        }
        if ($a == 0)
        {
            $response = 'Aucun dump à supprimer';
        }
        elseif ($a == 1) {
            $response = $a . ' dump a été supprimé';
        }
        else {
            $response = $a . ' dumps ont été supprimés';
        }
        $writeDump = new WriteDump();
        $writeDump->remplaceDumpFic($content, $dir_rep);
        return $response;
    }
}
