<?php

namespace RMA\Bundle\DumpBundle\Ftp;

interface FtpInterface 
{
    public function depotSurFTP($mode = FTP_ASCII);
    
    public function closeConnexionFTP();
}
