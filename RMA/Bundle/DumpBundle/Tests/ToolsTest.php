<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use RMA\Bundle\DumpBundle\Tools\Tools;

class ToolsTests extends \PHPUnit_Framework_TestCase
{
    
    
    public function testFormatDirWithFile()
    {
        
        $tools = new Tools;
        
        $case1_dir_win = 'rma\dump';
        $case2_dir_win = 'rma\dump\\';
 
        
        $fic = 'rma.log';
        
        $expected_win = 'rma\dump\rma.log';
              
        $this->assertEquals($expected_win, $tools->formatDirWithFile($case1_dir_win, $fic));
        $this->assertEquals($expected_win, $tools->formatDirWithFile($case2_dir_win, $fic));
      
    }
    
    public function testScanDirectory()
    {
        $tools = new Tools;
        $dir_test = __DIR__ .'/dossier_test';
        if (is_dir($dir_test))
        {
            $tools->rrMdir($dir_test);
        }
        
        mkdir($dir_test);
        mkdir($dir_test . '/test1');
        fopen($dir_test . '/test.txt', 'w+'); 
        
        $exclude = array();    
        $expected = array('test.txt', 'test1');
        $this->assertEquals($expected, $tools->removeFalseDir($dir_test, $exclude));
        
        fopen($dir_test . '/test_fic.ini', 'w+'); 
        $exclude2 = array ('test_fic.ini', 'test.txt');
        $expected2 = array('test1');
        $this->assertEquals($expected2, $tools->removeFalseDir($dir_test, $exclude2));
        
        $exclude3 = array('test1');
        $expected3 = array('test.txt', 'test_fic.ini');
        $this->assertEquals($expected3, $tools->removeFalseDir($dir_test, $exclude3));
        
        $tools->rrMdir($dir_test);
    }
    

}