<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Style\SymfonyStyle;

class CommonCommand extends ContainerAwareCommand {
    
    protected function configure() {
      
        $this->setName('rma:dump:help');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {            
       
        $io = new SymfonyStyle($input, $output);

        $io->title('Commandes disponibles dans le bundle rma :');

        $headers = array (
            'Commandes', 'Rôles', 'Options', 'Arguments'
        );
        $rows = array(
            array (
                'rma:dump:database', 
                'Cette commande permet de générer un dump',
                '--one, --i, --ftp, --name',
                '-'
            ),
            array (
                'rma:dump:clean',
                'Cette commande permet de nettoyer les dumps',
                '--nb_jour, --dir_dump, --nombre',
                '-'
            ),
            array (
                'rma:dump:sync',
                'Cette commande permet de synchroniser les métadatas',
                '--dir_dump',
                '-'
            ),
            array ('rma:dump:ftp', 
                'Cette commande permet d\'envoyer un dump par FTP',
                '-',
                '-'
            ),
            array ('rma:dump:cron', 
                'Cette commande est prévue spécialement pour être réalisée en CRON',
                '--host, --port, --username, --password, --compress, --zip, --dir_dump, ... ',
                'databases'
           )
        );
        $io->table($headers, $rows);
    }
    
    public function constructParamsArray (InputInterface $input)
    {
        $container = $this->getContainer();
        $params = array ();
        $params['date'] = date('Y-m-d-H\\hi');
        $params['repertoire_name'] = $params['date'] . '__' . uniqid();
        $params['logger'] = $container->get('logger');
        $params['extension'] = '.zip';
        $params['nb_jour'] = $container->getParameter('rma_nb_jour');
        $params['nombre_dump'] = $container->getParameter('rma_nombre_dump');

        $params['ftp'] = $container->getParameter('rma_ftp');
        $params['dir_dump'] = $container->getParameter('rma_dir_dump');
        $params['excludes'] = $container->getParameter('rma_excludes');
        
        $params = $this->loadOptions($input, $params);
        
        return $params;
    }
    
    public function loadOptions(InputInterface $input, Array $params)
    {
        $rOptions = $input->getOptions();
        $container = $this->getContainer();
     
        foreach ($rOptions as $rOption => $rvalue)
        {
            if($container->hasParameter('rma_'.$rOption))
            {
                $$rOption = $container->getParameter('rma_'.$rOption);
            }
            else 
            {
                $$rOption = null;
            }
            // On vérifie si une valeur a été transmise en option. Si c'est le cas on surcharge le parameters
            if (!is_null($rvalue))
            {
                $$rOption = $rvalue;
            }
            $params[$rOption] = $$rOption;
        }
        return $params;
    }
    
}
