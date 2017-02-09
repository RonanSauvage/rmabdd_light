<?php

namespace RMA\Bundle\DumpBundle\Tools;

use Psr\Log\LoggerInterface;

class DumpManager {
    
    protected $logger;
    
    public function __construct(LoggerInterface $logger){
        $this->logger = $logger;
    }
    
    /**
     * 
     * @param string $path
     * @return array ['timestamp']
     */
    public function getFileData($path)
    {
        $timestamp = filemtime($path);
        return array (
            "lastModification" => $timestamp
        );
    } 
}
