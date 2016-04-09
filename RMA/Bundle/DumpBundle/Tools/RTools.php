<?php

namespace RMA\Bundle\DumpBundle\Tools;

use Symfony\Component\DependencyInjection\ContainerAware;

use RMA\Bundle\DumpBundle\Tools\SyncDumpInterface;
use RMA\Bundle\DumpBundle\Tools\WriteDumpInterface;

/**
 * Description of RTools
 *
 * @author rmA
 */
class RTools extends ContainerAware {
    
    protected $_writedump;
    protected $_syncdump;

    public function __construct (WriteDumpInterface $writedump, SyncDumpInterface $syncdump)
    {
        $this->_syncdump = $syncdump;
        $this->_writedump = $writedump;
    }
    
    public function rmaDeleteOldDump($dir_rep, $jour)
    {
        return $this->_syncdump->deleteOldDump($dir_rep, $jour);
    }
}

