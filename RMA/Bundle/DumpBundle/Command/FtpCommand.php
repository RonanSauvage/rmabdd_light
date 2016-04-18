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
    
    public static function saveDumpInFtp ($io, $dump, Array $params)
    {
        if ($params['ftp'] == 'yes')
        {
            $io->title('Sauvegarde du dump sur le serveur FTP :');
            $dump->rmaDepotFTP();
            $io->success('Sauvegarde sur le serveur FTP réussie');
        }
    }
}
