<?php

namespace RMA\Bundle\DumpBundle\Tools;

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
    protected $_params;

    public function __construct (WriteDumpInterface $writedump, SyncDumpInterface $syncdump, ToolsInterface $tools, Array $params)
    {
        $this->_syncdump = $syncdump;
        $this->_writedump = $writedump;
        $this->_tools = $tools;
        $this->_params = $params;
    }

    /**
     * Permet de lancer la suppression des dumps de plus de $jours jours
     * @return array $response 
     */
    public function rmaDeleteOldDump()
    {
        if ($this->_params['nb_jour'] == 'none')
        {
            $this->_params['logger']->notice('Aucun clean n\'est prévu avec cette configuration');
        }
        elseif (!is_int($this->_params['nb_jour'])) {
            $this->_params['logger']->notice('La valeur pour le nombre de jours n\'est pas correcte : '. $this->_params['nb_jour']);
        }
        else {
            $response = $this->_syncdump->deleteOldDump($this->_params);
            $this->_params['logger']->notice('Les dumps de plus de '. $this->_params['nb_jour'] .' jours dans le répertoire '. $this->_params['dir_dump'] .' ont bien été supprimés');
        }
        return $response;
    }
    
    /**
     * Permet de lancer la suppression des dumps après un nombre 
     * @return string $message 
     */
    public function rmaDeleteDumpAfterThan()
    {
        if ($this->_params['nombre'] == 'none')
        {
            $this->_params['logger']->notice('Aucun clean n\'est pas prévu avec cette configuration');
        }
        elseif (!is_int($this->_params['nombre'])) {
            $this->_params['logger']->notice('La valeur pour le nombre de dump à conserver n\'est pas correcte : '. $this->_params['nombre']);
        }
        else {
            $response = $this->_syncdump->deleteDumpAfterThan($this->_params);
            $message = 'Les ' . $response['nombre'] . ' derniers dumps ont été convervés. Suppression de ' . $response['supprimes'] . ' dump(s) plus ancien(s)';
            $this->_params['logger']->notice($message);
        }
        return $message;
    }
    
    /**
     * Synchronise le fichier de sauvegarde selon les dumps encore présents dans le répertoire
     */
    public function rmaSyncRep()
    {
        $infos = $this->_syncdump->syncRep($this->_params['dir_dump']);
        $this->_writedump->remplaceDumpFic($infos["infos"], $this->_params['dir_dump']);
        $this->_params['logger']->notice('Le répertoire '. $this->_params['dir_dump'] .' a bien été synchronisé');
        return $infos;
    }
    
    /**
     * Méthode pour modifier un paramètre à n'importe quel moment (après initialisation notamment)
     * return l'objet mis à jour 
     */
    public function setParams($params_name, $value)
    {
        $this->_params[$params_name] = $value;
        return $this;
    }
}

