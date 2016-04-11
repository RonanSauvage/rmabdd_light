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
             ->setDescription('Permet de réaliser un dump en crontab. Si vous ne mettez pas d\'argument toutes les bases de données seront sauvegardées.')
             ->addOption(
               'host',
               '',
               InputOption::VALUE_OPTIONAL,
               'L\'ip de la connexion à la base de données.'
            )
            ->addOption(
               'port',
               '',
               InputOption::VALUE_OPTIONAL,
               'Le port d\'accès à la base de données.'
            )
            ->addOption(
               'username',
               '',
               InputOption::VALUE_OPTIONAL,
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
            ->addOption(
               'ftp_ip',
               '',
               InputOption::VALUE_OPTIONAL,
               'Ip du FTP'
            )
            ->addOption(
               'ftp_username',
               '',
               InputOption::VALUE_OPTIONAL,
               'Username pour le FTP'
            )
            ->addOption(
               'ftp_password',
               '',
               InputOption::VALUE_OPTIONAL,
               'Mot de passe pour le ftp'
            )
            ->addOption(
               'ftp_port',
               '',
               InputOption::VALUE_OPTIONAL,
               'Le port pour le FTP'
            )
            ->addOption(
               'ftp_timeout',
               '',
               InputOption::VALUE_OPTIONAL,
               'Le timeout paramétré pour le FTP'
            )
            ->addOption(
               'ftp_path',
               '',
               InputOption::VALUE_OPTIONAL,
               'Le path pour la sauvegarde sur le FTP'
            )
            ->addArgument(
                'databases',
                InputArgument::IS_ARRAY,
                'Les bases de données à sauvegarder séparés par des espaces.'
            );
                
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {        
        $container = $this->getContainer();

        $params = $this->hydrateInputOptions($input);
        
        // A gérer l'extension pour le FTP
        $params['extension'] = '.zip';
        $params['dir_fichier'] = $params['dir_zip']; 
        
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
            throw new \Exception ('Aucune base de données detectée avec les paramètres définis');
        }

        $dump->rmaDumpForDatabases($databases);
        
        FtpCommand::saveDumpInFtp($params['ftp'], $dump);

        $nb_jour = $container->getParameter('rma_nb_jour');
        CleanDumpCommand::cleanCommand($output, $params['dir_dump'], $nb_jour);
    }


    /**
     * Permet d'hydrater l'array Params selon les options définies au niveau de la commande
     * @param InputInterface $input
     * @return array $params
     */
    public function hydrateInputOptions (InputInterface $input)
    {
        $rOptions = $input->getOptions();
        $container = $this->getContainer();
        $params = array ();
        $params['repertoire_name'] = date('Y-m-d-H\\hi') . '__' . uniqid();
        $params['logger'] = $container->get('logger');
        foreach ($rOptions as $rOption => $rvalue)
        {
            if($container->hasParameter('rma_'.$rOption))
            {
                $$rOption = $container->getParameter('rma_'.$rOption);
            }
            if (!is_null($rvalue))
            {
                $$rOption = $rvalue;
            }
            $params[$rOption] = $$rOption;
        }
        return $params;
    }
}
