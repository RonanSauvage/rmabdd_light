<?php

namespace RMA\Bundle\DumpBundle\Factory;

use RMA\Bundle\DumpBundle\Tools\SyncDump;
use RMA\Bundle\DumpBundle\Tools\Tools;
use RMA\Bundle\DumpBundle\Tools\WriteDump;
use RMA\Bundle\DumpBundle\Tools\RTools;

/**
 * Description of RToolsFactory
 *
 * @author rma
 */
class RToolsFactory{
        
    /**
     * Factory pour l'utilisation de RTools
     * @param Array $params
     * @return \RMA\Bundle\DumpBundle\Factory\RTools
     */
    public static function create(Array $params)
    {
        $writedump = new WriteDump();
        $syncdump = new SyncDump();
        $tools = new Tools();
        return new RTools($writedump, $syncdump, $tools, $params);
    }  
}
