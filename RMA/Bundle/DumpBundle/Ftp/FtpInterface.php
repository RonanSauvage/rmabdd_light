<?php

namespace RMA\Bundle\DumpBundle\Ftp;

interface FtpInterface 
{
    public function DepotSurFTP($mode = FTP_ASCII);
    
    public function CloseConnexionFTP();
}

