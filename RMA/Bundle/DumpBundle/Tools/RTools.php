<?php

namespace RMA\Bundle\DumpBundle\Tools;

use Psr\Log\LoggerInterface;

use RMA\Bundle\DumpBundle\Tools\SyncDumpInterface;
use RMA\Bundle\DumpBundle\Tools\WriteDumpInterface;
use RMA\Bundle\DumpBundle\Tools\ToolsInterface;


/**
 * Description of RTools
 *
 * @author rmA
 */
class RTools {
    
    protected $_writedump;
    protected $_syncdump;
    protected $_tools;
    protected $_logger;

    public function __construct (WriteDumpInterface $writedump, SyncDumpInterface $syncdump, ToolsInterface $tools, LoggerInterface $logger)
    {
        $this->_syncdump = $syncdump;
        $this->_writedump = $writedump;
        $this->_tools = $tools;
        $this->_logger = $logger;
    }

    /**
     * Permet de lancer la suppression des dumps
     * @param string $dir_rep
     * @param int $jour
     * @return mixed
     */
    public function rmaDeleteOldDump($dir_rep, $jour)
    {
        $response = $this->_syncdump->deleteOldDump($dir_rep, $jour);
        $this->_logger->notice('Les dumps de plus de '. $jour .' jours dans le répertoire '. $dir_rep .' ont bien été supprimés');
        return $response;
    }
    
    /**
     * Synchronise le fichier de sauvegarde selon les dumps encore présents dans le répertoire
     * @param string $dir_rep
     */
    public function rmaSyncRep($dir_rep)
    {
        $infos = $this->_syncdump->syncRep($dir_rep);
        $this->_writedump->remplaceDumpFic($infos["infos"], $dir_rep);
        $this->_logger->notice('Le répertoire '. $dir_rep .' a bien été synchronisé');
        return $infos;
    }
}

