<?php

namespace RMA\Bundle\DumpBundle\Factory;

use RMA\Bundle\DumpBundle\Dump\RMADump;
use RMA\Bundle\DumpBundle\ConnexionDB\ConnexionDB;
use RMA\Bundle\DumpBundle\Zip\RZip;
use RMA\Bundle\DumpBundle\Dump\DumpMysql;
use RMA\Bundle\DumpBundle\Ftp\Rftp;
use RMA\Bundle\DumpBundle\Tools\WriteDump;

/**
 * Description of RDumpFactory
 *
 * @author rma
 */
class RDumpFactory{
        
    /**
     * Factory pour l'utilisation de RMADump
     * @param array $params
     * @return \RMA\Bundle\DumpBundle\Dump\RMADump
     */
    public static function create(array $params)
    {
        $connexiondb = new ConnexionDB($params);
        $rzip = new RZip($params['dir_zip']);
        $mysqldump = new DumpMysql($connexiondb, $params); 
        $rftp = new Rftp($params);
        $writedump = new WriteDump();
        return new RMADump($connexiondb, $mysqldump, $rzip, $params['zip'], $rftp, $params['logger'], $writedump);
    }  
}
