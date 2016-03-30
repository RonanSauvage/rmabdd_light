<?php

namespace RMA\Bundle\DumpBundle\Factory;

use RMA\Bundle\DumpBundle\Dump\RMADump;
use RMA\Bundle\DumpBundle\ConnexionDB\ConnexionDB;
use RMA\Bundle\DumpBundle\Zip\RZip;
use RMA\Bundle\DumpBundle\Dump\DumpMysql;
use RMA\Bundle\DumpBundle\Ftp\Rftp;

/**
 * Description of RDumpFactory
 *
 * @author rma
 */
class RDumpFactory{
        
    /**
     * Factory pour l'utilisation de RMADump
     * @param array $params
     * @return \RMA\Bundle\DumpBundle\Factory\RMADump
     */
    public static function create(array $params)
    {
        $connexiondb = new ConnexionDB($params);
        $rzip = new RZip($params['dir_zip']);
        $mysqldump = new DumpMysql($connexiondb, $params); 
        $rftp = new Rftp($params);
        return new RMADump($connexiondb, $mysqldump, $rzip, $params['zip'], $rftp);
    }  
}