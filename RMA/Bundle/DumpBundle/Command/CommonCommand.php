<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class CommonCommand extends ContainerAwareCommand {
    
    protected function configure() {
      
        $this->setName('rma:dump:configuration');
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

        $params['ftp'] = $container->getParameter('rma_ftp');
        $params['dir_dump'] = $container->getParameter('rma_dir_dump');
        $params['excludes'] = $container->getParameter('rma_excludes');
       
        return $params;
    }
    
}
