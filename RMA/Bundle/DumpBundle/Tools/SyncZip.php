<?php

namespace RMA\Bundle\DumpBundle\Tools;

use RMA\Bundle\DumpBundle\Tools\Tools;

use RMA\Bundle\DumpBundle\Tools\WriteDump;

/**
 * Description of SyncZip
 *
 * @author rmA
 */
class SyncZip {

    public function deleteOldZip($params)
    {
        $tools = new Tools();
        $rep_zip = $tools->scanDirectory($params['dir_zip']);
        var_dump($rep_zip);
    }
    
}
