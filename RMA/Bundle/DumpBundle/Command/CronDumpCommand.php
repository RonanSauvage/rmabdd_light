<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use RMA\Bundle\DumpBundle\Factory\RDumpFactory;

class CronDumpCommand extends ContainerAwareCommand {
    
    protected function configure() {
      
        $this->setName('rma:dump:cron')
             ->setDescription('Permet de réaliser un dump en crontab. Si vous ne mettez pas d\'argument toutes les bases de données seront sauvegardées. '
                     . 'Pour utiliser la commande vous devez indiquer les options suivantes : '
                     . 'Host de la connection --host='
                     . 'Port de la connexion --port='
                     . 'Le username --username='
                     . 'Le password --password='
                     . 'La compression --compress={Gzip, Bzip2}'
                     . 'Le zip --zip')
             ->addOption(
               'host',
               '',
               InputOption::VALUE_REQUIRED,
               'L\'ip de la connexion à la base de données.'
            )
            ->addOption(
               'port',
               '',
               InputOption::VALUE_REQUIRED,
               'Le port d\'accès à la base de données.'
            )
            ->addOption(
               'username',
               '',
               InputOption::VALUE_REQUIRED,
               'L\'username de connexion à la base de données.'
            )
            ->addOption(
               'password',
               '',
               InputOption::VALUE_OPTIONAL,
               'Le password de connexion.'
            )
            ->addOption(
               'compress',
               '',
               InputOption::VALUE_OPTIONAL,
               'Permet de compresser les dumps'
            )
            ->addOption(
               'zip',
               '',
               InputOption::VALUE_OPTIONAL,
               'Permet de zipper le résultat du dump'
            )
            ->addOption(
               'dir_dump',
               '',
               InputOption::VALUE_OPTIONAL,
               'Le lien vers le dossier de dump'
            )
            ->addOption(
               'dir_zip',
               '',
               InputOption::VALUE_OPTIONAL,
               'Le lien vers le dossier de zip'
            )
            ->addOption(
               'ftp',
               '',
               InputOption::VALUE_OPTIONAL,
               'Permet de transférer le dump en FTP'
            )
            ->addArgument(
                'databases',
                InputArgument::IS_ARRAY,
                'Les bases de données à sauvegarder séparés par des espaces.'
            );
                
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {        
        $params = array();
        $params['repertoire_name'] = date('Y-m-d-H\\hi') . '__' . uniqid();  
                
        $options = array(
            'host'     ,
            'port'     , 
            'username' ,
            'password' ,
            'compress' ,
            'zip'      ,
            'dir_dump' ,
            'dir_zip'  ,
            'ftp_ip'   ,
            'ftp_username'  ,
            'ftp_password'  ,
            'ftp_port'      ,
            'ftp_timeout'   ,
            'ftp_path'     );
      
        foreach($options as $option)
        {
            $$option = $this->getContainer()->getParameter('rma_'.$option);
            if (!is_null($input->getOption($option)))
            {
                $$option = $input->getOption($option);
            }
            $params[$option] = $$option;
        }

        // On gère le mot de passe pouvant être vide 
        if(($input->getOption('password'))== 'none')
        {
            $params['password'] = '';
        }
        
        $dump = RDumpFactory::create($params);
        $databases = $dump->rmaDumpGetListDatabases(); 
        
        if ($input->getArgument('databases')) 
        {
            $databases = $input->getArgument('databases');
        }    
   
        // On vérifie que la connexionDB contienne au moins 1 base de données
        if (count($databases) == 0) {
            throw new Exception ('Aucune base de données detectée avec les paramètres définis');
        }
 
        $dump->rmaDumpForDatabases($databases);
        
        if ($params['ftp'] && $params['ftp'] !== 'false') 
        {
            $dump->rmaDepotFTP();
        }
    }
}
