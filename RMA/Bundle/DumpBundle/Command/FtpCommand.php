<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;


class FtpCommand extends ContainerAwareCommand {

    protected function configure() {

        $this->setName('rma:dump:ftp')
            ->setDescription("Permet d'envoyer un dump sur le serveur ftp");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
    }
    
    public static function saveDumpInFtp ($ftp, $dump)
    {
        if ($ftp == 'yes')
        {
            $dump->rmaDepotFTP();
        }
    }
}
