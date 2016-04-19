<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class CommonCommand extends ContainerAwareCommand {
    
    protected function configure() {
      
        $this->setName('rma:dump:help');
    }
    /*
    protected function execute(InputInterface $input, OutputInterface $output) 
    {            
        $params = $this->hydrateCommand($input);
        $io = new SymfonyStyle($input, $output);

        $io->title('Commandes disponibles dans le bundle rma :');

        $headers = array (
            'Commandes', 'Fonctionnement', 'Options', 'Paramètres'
        );
        $rows = array(
            array ('rma:dump:database', 'Cette commande permet de générer un dump des différents schémas de la base de données','',''),
            array ('rma:dump:clean', 'Cette commande permet de nettoyer les dumps','',''),
            array ('rma:dump:sync', 'Cette commande permet de synchroniser les métadatas','',''),
            array ('rma:dump:ftp', 'Cette commande permet d\'envoyer un dump par FTP','',''),
            array ('rma:dump:cron', 'Cette commande est prévue spécialement pour être réalisée en CRON','','')
            
        );
        $io->table($headers, $rows);

        $io->note('Nombre de dumps effacés des logs : ' . $infos['synchro']);
    }*/
    
    public function constructParamsArray (InputInterface $input)
    {
        $container = $this->getContainer();
        $params = array ();
        $params['date'] = date('Y-m-d-H\\hi');
        $params['repertoire_name'] = $params['date'] . '__' . uniqid();
        $params['logger'] = $container->get('logger');
        $params['extension'] = '.zip';
        $params['nb_jour'] = $container->getParameter('rma_nb_jour');
        $params['nombre'] = $container->getParameter('rma_nombre_dump');

        $params['ftp'] = $container->getParameter('rma_ftp');
        $params['dir_dump'] = $container->getParameter('rma_dir_dump');
        $params['excludes'] = $container->getParameter('rma_excludes');
       
        return $params;
    }
    
}
