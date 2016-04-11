<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;


class FtpCommand extends ContainerAwareCommand {

    protected function configure() {

        $this->setName('rma:dump:ftp')
            ->setDescription("Permet d'envoyer un dump sur le serveur ftp")
            ->addOption(
                'not-all',
                false,
                InputOption::VALUE_NONE,
                'Si not-all est spécifié, vous devrez sélectionner la base de données à dump'
            )
            ->addOption(
                'i',
                false,
                InputOption::VALUE_NONE,
                'Si i est spécifié, vous aurez des intéractions pour sélectionner les données à dump. Sinon les parameters sera pris. '
            )
            ->addOption(
                'ftp',
                false,
                InputOption::VALUE_NONE,
                'Si ftp est spécifié, le dump sera sauvegardé sur le serveur FTP défini en paramètre ou dans les interactions avec i. '
            )
            ->addOption(
                'name',
                false,
                InputOption::VALUE_REQUIRED,
                'Permet de définir un nom pour le dossier de sauvegarde. '
            );
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
