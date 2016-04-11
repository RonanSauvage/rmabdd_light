<?php

namespace RMA\Bundle\DumpBundle\Tools;

use Symfony\Component\DependencyInjection\ContainerAware;

use RMA\Bundle\DumpBundle\Tools\SyncDumpInterface;
use RMA\Bundle\DumpBundle\Tools\WriteDumpInterface;
use RMA\Bundle\DumpBundle\Tools\ToolsInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;



/**
 * Description of RTools
 *
 * @author rmA
 */
class RTools extends ContainerAware {
    
    protected $_writedump;
    protected $_syncdump;
    protected $_tools;

    public function __construct (WriteDumpInterface $writedump, SyncDumpInterface $syncdump, ToolsInterface $tools)
    {
        $this->_syncdump = $syncdump;
        $this->_writedump = $writedump;
        $this->_tools = $tools;
    }

    /**
     * Permet de lancer la suppression des dumps
     * @param string $dir_rep
     * @param int $jour
     * @return mixed
     */
    public function rmaDeleteOldDump($dir_rep, $jour)
    {
        return $this->_syncdump->deleteOldDump($dir_rep, $jour);
    }
}

