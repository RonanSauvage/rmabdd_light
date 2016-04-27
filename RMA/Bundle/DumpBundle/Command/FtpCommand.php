<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class FtpCommand extends CommonCommand {

    protected function configure() {

        $this->setName('rma:dump:ftp')
            ->setDescription("Permet d'envoyer un dump sur le serveur ftp")
            ->addOption('dir_dump', '', InputOption::VALUE_OPTIONAL, 'Permet de définir le répertoire dans lequel est cherché le dump. Si pas spécifié, on prend le répertoire dans parameters');      
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {        
        // Prévoir l'ajout d'options pour sélectionner un autre serveur FTP que celui défini au niveau des params
 
        // On affiche les fichiers et/ou répertoires présents dans ce dossier
        
        // On demande la sélection du fichier et ou répertoire à zip
        
        // On vérifie si la sélection est un répertoire
        // Dans ce cas on zip le répertoire et on peut sauvegarder l'extension comme zip
        
        // Sinon on récupère l'extension et on l'envoie sur le serveur FTP
    }
    
    /**
     * Réalise la sauvegarde sur le serveur FTP
     * @param SymfonyStyle $io
     * @param RMADump $dump
     * @param array $params
     */
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
