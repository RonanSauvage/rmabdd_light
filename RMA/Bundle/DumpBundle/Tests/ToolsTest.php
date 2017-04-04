<?php 

namespace RMA\Bundle\DumpBundle\Tests;

use RMA\Bundle\DumpBundle\Tools\Tools;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @package RMA\Bundle\DumpBundle\Tests
 */
class ToolsTest extends WebTestCase { 

    /**
     * Test de la fonction static pour clean une string. 
     * @param string $stringToClean
     * @param string $expected
     * @dataProvider getDataProviderCleanString
    */
    public function testCleanString($stringToClean, $expectedWithoutUnderscore, $expectedWithUnderscore){

        $res = Tools::cleanString($stringToClean);
        $this->assertEquals($expectedWithoutUnderscore, $res);
        
        $res = Tools::cleanString($stringToClean, true);
        $this->assertEquals($expectedWithUnderscore, $res);
    }
    
    /**
     * 
     * @return array 
     */
    public function getDataProviderCleanString(){
        return array (
            array(
                'Ronan',
                'Ronan',
                'Ronan'
            ),
            array(
                '&ù&Ronàn',  
                'u-Ronan',
                '&u&Ronan'
            ),
            array(
                'ronan_database',
                'ronan-database',
                'ronan_database'
            )
        );
    }

    /**
     * 
     * @param type $first
     * @param type $second
     * @param type $expected
     * @dataProvider getDataProviderFormatDir
     */
    public function testFormatDirWithFile($first, $second, $expected){
        $res = Tools::formatDirWithFile($first, $second);
        $this->assertEquals($expected, $res);
    }
    
    /**
     * 
     * @return array 
     */
    public function getDataProviderFormatDir(){
        return array (
            array (
                'http://ronan.dev',
                'app_dev.php',
                'http://ronan.dev/app_dev.php'
            ),
            array(
                'http://ronan.dev/',
                'app_dev.php',
                'http://ronan.dev/app_dev.php'
            ),
            array(
                'http://ronan.dev',
                '/app_dev.php',
                'http://ronan.dev//app_dev.php'
            )  
        );
    }
    
    /**
     * Permet de valider la méthode de retrait des fichiers non souhaités
     */
    public function testRemoveFalseDir(){
        
        $nameFile = 'test.php';
        
        $pathFolder = $this->initFolder();
        $file1 = $pathFolder. '/'. $nameFile;
        
        // Par défaut avec scandir on a '.' et '..'
        $filesWithoutFunction = scandir($pathFolder);
        $this->assertEquals(count($filesWithoutFunction), 2);
        
        // On vérifie que la méthode nous retourne bien 0. Cela signfie qu'elle exclut correctement '.' et '..'
        $files = Tools::removeFalseDir($pathFolder, array('exclude')); 
        $this->assertEquals(count($files), 0);
        
        // On crée le fichier
        fopen($file1, 'a+');
      
        // On teste à nouveau après avoir créé un fichier
        $filesAfterAdd = Tools::removeFalseDir($pathFolder, array('exclude')); 
        $this->assertEquals(count($filesAfterAdd), 1);
        
        // On envoie le fichier créé en exclude et vérifions qu'il n'apparait plus
        $filesWithoutAdded = Tools::removeFalseDir($pathFolder, array('exclude', $nameFile)); 
        $this->assertEquals(count($filesWithoutAdded), 0);
        
        // On doit ici avoir 3 entrées : '.', '..' et le fichier créé
        $allFiles = scandir($pathFolder);
        $this->assertEquals(count($allFiles), 3);
        
        // On nettoie l'environnement
        unlink($file1);
        rmdir($pathFolder);
    }
    
    /**
     * Permet de valider la méthode récursive ScanDir
     * Si vous rencontrer des problèmes comme dir not empty, vous pouvez supprimer le répertoire folderForTests
     */
    public function testScanDir(){
 
        $tools = new Tools();
        
        $pathFolder = $this->initFolder();
        $nameFolderToFolder = '/foldertoFolder';
        $pathFolderToFolder = $pathFolder . $nameFolderToFolder;
        
        $dirs = $tools->scanDirectory($pathFolder);
        $this->assertEquals(count($dirs), 0);
         
        $pathFolderToFolder = $this->initFolder('folderToFolder', '/test2.php', $pathFolder);
 
        $pathFile = $pathFolderToFolder . '/test2.php';
        
        $dirs = $tools->scanDirectory($pathFolder);
        $this->assertEquals(count($dirs), 1);
        
        // On crée le fichier
        fopen($pathFile, 'a+');
        
        $dirs = $tools->scanDirectory($pathFolder);
        $this->assertEquals(count($dirs), 1);
        
        /**
         * On initialise un second dossier dans le premier niveau
         */
        $pathFolderToFolder2 = $this->initFolder('folderToFolder2', '/test2.php', $pathFolder);
         $dirs = $tools->scanDirectory($pathFolder);
         
        unlink($pathFile);
        rmdir($pathFolderToFolder2);
        rmdir($pathFolderToFolder);
        rmdir($pathFolder);
        
    }
    
    public function initFolder($nameFolder = 'folderForTest', $nameFile = 'test.php', $dir = __DIR__){
        $nameFolder = '/' . $nameFolder;
        $pathFolder = $dir . $nameFolder;
        $file1 = $pathFolder. '/'. $nameFile;
        
        // Si des fichiers existent déjà d'un ancien test, nous les nettoyons
        if(file_exists($pathFolder)){
            if(file_exists($file1)){
                unlink($file1);
            }
            rmdir($pathFolder);
        }
        
        // On cré le folder
        mkdir($pathFolder);
        
        return $pathFolder;
    }
}