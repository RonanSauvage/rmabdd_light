<?php

namespace RMA\Bundle\DumpBundle\Tools;

use RMA\Bundle\DumpBundle\Tools\Tools;

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
        return $rep_zip;
    }   
}
