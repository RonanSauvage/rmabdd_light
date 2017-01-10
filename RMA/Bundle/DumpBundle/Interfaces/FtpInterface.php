<?php

namespace RMA\Bundle\DumpBundle\Interfaces;

interface FtpInterface 
{
    public function depotSurFTP($mode = FTP_ASCII);
    
    public function closeConnexionFTP();
}
