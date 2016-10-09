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
        $count_initial = count($content);
       
        // On récupère tous les répertoires de  dump dans le répertoire envoyé en paramètre
        $contenu_content_rep = array_values(array_diff(scandir($dir_rep), array('..', '.', self::NAME_DUMP)));
        
        $my_array = array ();       
        foreach ($content as $name_dump => $data_dump)
        {
            $resultats = explode('|', $name_dump);
            foreach ($contenu_content_rep as $dump_in_dir)
            {
                if ($dump_in_dir == trim($resultats[2])){
                    $my_array[$name_dump] = $data_dump;
                }
            }          
            
        }
        $count_final = count($my_array);
        return array (
            "infos"         => $my_array,
            "count_initial" => $count_initial,
            "count_final"   => $count_final,
            "synchro"       => $count_initial - $count_final
        );
    }
    
    
    /**
     * Permet d'initialiser une array avec les données dans un fichier
     * Ou vide si le fichier n'existe pas
     * @param string $dir_rep
     * @return array $content
     */
    private function recupData($dir_rep)
    {
        $fic = Tools::formatDirWithFile($dir_rep, self::NAME_DUMP);
        $content = array();
        // On vérifie que le fichier existe
        if (file_exists($fic)){
            $content = parse_ini_file($fic, true);
        }  
        return $content;
    }

    /**
     * Permet de supprimer les dumps plus anciens que le nombre de jours envoyé
     * @param Array $params
     */
    public function deleteOldDump(Array $params)
    {
        $date = new \Datetime();
        $date->sub(new \DateInterval('P'.$params['nb_jour'].'D'));
        return $this->selectDumpWithDate($params['dir_dump'], $date);
    }
    
    /**
     * Permet de sélectionner les dump plus anciens que la date sélectionnée
     * Suppression du dump 
     * @param string $dir_rep
     * @param \Datetime $date
     */
    public function selectDumpWithDate($dir_rep, \Datetime $date)
    {
        $content = $this->recupData($dir_rep); 
        $a = 0;
        foreach ($content as $dump => $data)
        {
            $datas = Tools::getArrayDump($dump);
            $date_dump = new \Datetime(substr($datas['date'], 0, 10));
            if ($date_dump < $date)
            {
                $path_dump_to_delete = Tools::formatDirWithFile($dir_rep , $datas['identifiant']);
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
    
    /**
     * Permet de supprimer les dumps au dessus d'un certain nombre envoyé en paramètre $params['nombre_dump']
     * On synchronise le fichier de logs de dump après suppression
     * @param array $params
     * @return array
     */
    public function deleteDumpAfterThan (Array $params)
    {  
        // On récupère les logs liés au répertoire de dump
        $array_fic_dump_ini = $this->recupData($params['dir_dump']);
        
        $count_array_fic_dump_ini = count( $array_fic_dump_ini );
        $a = 0;

        // On renverse l'array pour avoir un ordre chronologique
        $array_after_reverse = array_reverse($array_fic_dump_ini);
        // On coupe l'array selon le nombre de dump - 1 à conserver (car commence à 0)
        $array_after_splice_for_delete = array_splice($array_after_reverse, $params['nombre_dump'] - 1, $count_array_fic_dump_ini);
       
        // On parcourt chaque entrée pour récupérer l'identifiant / nom du répertoire
        foreach ($array_after_splice_for_delete as $name_dump => $data_dump)
        {
            $resultats = explode('|', $name_dump);
            $path_dump_to_delete = Tools::formatDirWithFile($params['dir_dump'] , trim($resultats[2]));
           
            Tools::rrmdir($path_dump_to_delete);
            $a += 1;
        }  
        // On resynchronise le fichier de logs lié au répertoire de dump pour mettre à jour les suppression   
        $this->syncRep($params['dir_dump']);
        $resultats = array (
            "nombre_dump"    => $params['nombre_dump'],
            "supprimes" => $a
        );
        return  $resultats;
    }
   
}
