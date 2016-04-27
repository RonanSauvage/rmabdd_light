<?php

namespace RMA\Bundle\DumpBundle\Zip;

use RMA\Bundle\DumpBundle\Zip\ZipInterface;

/**
 * Description of RZip
 *
 * @author rma
 */
class RZip implements ZipInterface {

    protected $_pathZip;
    
    /**
     * Construct de la classe RZip
     * @param string $dir
     */
    public function __construct($dir)
    {
        $this->_pathZip = $dir;
       
    }
    
    /** 
     * Ajouter fichier et répertoire contenu dans le dossier à zipper
     * @param string $folder 
     * @param ZipArchive $zipFile 
     * @param int $exclusiveLength  
     */ 
    public static function folderToZip($folder, &$zipFile, $exclusiveLength) { 
        $handle = opendir($folder); 
        while (false !== $f = readdir($handle)) { 
            if ($f != '.' && $f != '..') { 
                $filePath = "$folder/$f"; 
                $localPath = substr($filePath, $exclusiveLength); 
                // Si c'est un fichier
                if (is_file($filePath)) { 
                    $zipFile->addFile($filePath, $localPath); 
                } 
                // Sinon c'est un dossier
                else 
                { 
                    $zipFile->addEmptyDir($localPath); 
                    // récursif
                    self::folderToZip($filePath, $zipFile, $exclusiveLength); 
                } 
            } 
        } 
        closedir($handle); 
    } 

    /** 
     *  Permet de lancer un zip 
     *  RZip::rZip('/path/to/sourceDir', '/path/to/out.zip'); 
     *  @param string $sourcePath   
     */ 
    public function execZip($sourcePath) { 
        if (!file_exists($this->getPathZip())){
            mkdir($this->getPathZip());
        }  
        $pathInfo = pathInfo($sourcePath); 
        $parentPath = $pathInfo['dirname']; 
        $dirName = $pathInfo['basename']; 
        
        $z = new \ZipArchive(); 
        $z->open($this->getPathZip(). DIRECTORY_SEPARATOR . $dirName.'.zip' , \ZIPARCHIVE::CREATE  | \ZIPARCHIVE::OVERWRITE); 
        $z->addEmptyDir($dirName); 
        self::folderToZip($sourcePath, $z, strlen("$parentPath/")); 
        $z->close(); 
    } 
    
    /**
     * Getter du chemin ZIP
     * @return string $pathZip
     */
    public function getPathZip()
    {
        return $this->_pathZip;
    }
} 
