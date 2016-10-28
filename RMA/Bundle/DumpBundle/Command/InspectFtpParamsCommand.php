<?php

namespace RMA\Bundle\DumpBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use RMA\Bundle\DumpBundle\Ftp\Rftp;

class InspectFtpParamsCommand extends CommonCommand {
    
    protected function configure() {
      
        $this->setName('rma:dump:inspectFtps')
            ->setDescription("Permet d'inspecter les paramètres définis pour les FTPs")
            ->setAliases(['inspectFtps']);       
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {          
        $io = new SymfonyStyle($input, $output);

        $fields = Rftp::getFields();
        
        // On charge l'array params avec les options / parameters
        $params = $this->constructParamsArray($input, array('Ftps' => $fields));
        
        $io->title('Description des connexions au FTP à partir des paramètres définis  :');

        $headers = array (
            'Nom de la connexion ftp', 'Ip', 'Port', 'Timeout', 'Path', 'Username', 'Password'
        );

        $rows = array();
        $ftp_array = array ();
        foreach ($params['ftps'] as $ftp)
        {             
            $ftp_array = array(
                $ftp['name_ftp'], $ftp['ftp_ip'], $ftp['ftp_port'], $ftp['ftp_timeout'], $ftp['ftp_path'], 
                $ftp['ftp_username'], $ftp['ftp_password'] 
            );
            array_push($rows, $ftp_array);
        }
        $io->table($headers, $rows);
    }    
}
