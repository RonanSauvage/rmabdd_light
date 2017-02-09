<?php

namespace RMA\Bundle\DumpBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class RMADumpExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new RMAConfiguration();
        $configsMerged = $this->processConfiguration($configuration, $configs);
      
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml'); 
        $loader->load('config.yml'); 
       
        foreach($configsMerged as $configMerged => $value)
        {
            if(!is_array($configMerged)){
                $container->setParameter($configMerged, $value);
            }
        }
        
        $this->addClassesToCompile(array(
            'RMA\\Bundle\\DumpBundle\\Tools\\SyncDump',
            'RMA\\Bundle\\DumpBundle\\Tools\\WriteDump',
            'RMA\\Bundle\\DumpBundle\\Tools\\SyncZip', 
            'RMA\\Bundle\\DumpBundle\\Tools\\Tools',
            'RMA\\Bundle\\DumpBundle\\Tools\\RTools',
            'RMA\\Bundle\\DumpBundle\\Tools\\DatabaseManager',
            'RMA\\Bundle\\DumpBundle\\Ftp\\Rftp',
            'RMA\\Bundle\\DumpBundle\\Factory\\RDumpFactory',
            'RMA\\Bundle\\DumpBundle\\Factory\\RToolsFactory',
            'RMA\\Bundle\\DumpBundle\\ConnexionDB\\ConnexionDB',
            'RMA\\Bundle\\DumpBundle\\Dump\\DumpMysql',
            'RMA\\Bundle\\DumpBundle\\Dump\\RMADump'
        ));
    }
    
    public function getAlias()
    {
        return "rma_dump";
    }
}
