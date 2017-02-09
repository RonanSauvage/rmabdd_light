<?php

namespace RMA\Bundle\DumpBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class RMAConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('rma_dump');

        $rootNode
            ->children()       
                ->enumNode('rma_compress')->values(array('none', 'gzip', 'bzip2'))->defaultValue('none')->end()
                ->enumNode('rma_zip')->values(array('yes', 'no'))->defaultValue('no')->end()
                ->enumNode('rma_keep_tmp')->values(array('yes', 'no'))->defaultValue('no')->end()
                ->scalarNode('rma_dir_zip')->defaultValue('rmabundle/zip')->end()
                ->scalarNode('rma_dir_dump')->defaultValue('rmabundle/dump')->end()
                ->scalarNode('rma_dir_tmp')->defaultValue('rmabundle/tmp')->end()
                ->scalarNode('rma_dir_export')->defaultValue('rmabundle/export')->end()
                ->scalarNode('rma_dir_script_migration')->defaultValue('rmabundle/script')->end()
                ->scalarNode('rma_nb_jour')->defaultValue(7)->end()
                ->scalarNode('rma_keep_tmp')->defaultValue('nod')->end()
                ->integerNode('rma_nombre_dump')->defaultValue(8)->min(0)->end()
                ->scalarNode('rma_script')->defaultValue('defaultScript.sql')->end()
                ->enumNode('rma_ftp')->values(array('yes', 'no'))->defaultValue('no')->end()
                ->arrayNode('rma_ftps')
                    ->useAttributeAsKey('rma_name_ftp')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('rma_name_ftp')->defaultValue('my_connexion')->end()
                            ->scalarNode('rma_ftp_ip')->defaultValue('127.0.0.1')->end()
                            ->scalarNode('rma_ftp_username')->defaultValue('rmausername')->end()
                            ->scalarNode('rma_ftp_password')->defaultValue('rmapassword')->end()
                            ->booleanNode('rma_ftp_port')->defaultValue('21')->end()
                            ->booleanNode('rma_ftp_timeout')->defaultValue('90')->end()
                            ->scalarNode('rma_ftp_path')->defaultValue('/home/rma/dump')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('rma_connexions')
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('rma_name')->defaultValue('my_connexion')->end()
                            ->scalarNode('rma_driver')->defaultValue('pdo_mysql')->end()
                            ->scalarNode('rma_host')->defaultValue('127.0.0.1')->end()
                            ->integerNode('rma_port')->defaultValue('3306')->end()
                            ->scalarNode('rma_user')->defaultValue('root')->end()
                            ->scalarNode('rma_password')->defaultValue('')->end() 
                        ->end()
                    ->end()
                ->end() 
            ->end();
        return $treeBuilder;
    }
}
    