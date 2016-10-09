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
                ->scalarNode('rma_host')
                    ->defaultValue('127.0.0.1')
                    ->info('This value is used to connect database. She is required & cannot be empty')
                ->end()
                ->integerNode('rma_port')
                    ->defaultValue('3306')
                    ->info('This value is the port & is used to connect database. She cannot be empty')
                ->end()
                ->scalarNode('rma_user')
                    ->defaultValue('root')
                    ->info('This value is the username & is used to connect database. She cannot be empty')
                ->end()
                ->scalarNode('rma_password')
                    ->defaultValue('')
                    ->info('This value is the password & is used to connect database. She can be empty')
                ->end() 
                ->arrayNode('rma_excludes')
                    ->prototype('scalar')
                    ->info('This value is an array. She is used to exclude databases')
                    ->defaultValue('mysql, information_schema, performance_schema')
                ->end()
                ->append($this->addParametersNode())
                ->append($this->addParametersNodeFTP())
                ->append($this->addParametersNodeClass())
            ->end();
        return $treeBuilder;
    }
    
    /**
     * Permet de configurer la partie Others du fichier de config
     * @return TreeBuilder
     */
    public function addParametersNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('others');

        $node
            ->canBeEnabled()
            ->children()
                ->enumNode('rma_compress')
                    ->values(array('none', 'gzip', 'bzip2'))
                    ->defaultValue('none')
                ->end()
                ->enumNode('rma_zip')
                    ->values(array('yes', 'no'))
                    ->defaultValue('no')
                ->end()
                ->scalarNode('rma_dir_zip')
                    ->defaultValue('')
                ->end()
                ->scalarNode('rma_dir_dump')
                    ->defaultValue('')
                ->end()
                ->scalarNode('rma_dir_tmp')
                    ->defaultValue('')
                ->end()
                ->scalarNode('rma_dir_export')
                    ->defaultValue('')
                ->end()
                ->scalarNode('rma_dir_script_migration')
                    ->defaultValue('')
                ->end()
                ->scalarNode('rma_dir_dump')
                    ->defaultValue('')
                ->end()
                ->scalarNode('rma_nb_jour')
                    ->defaultValue(7)
                ->end()
                ->integerNode('rma_nombre_dump')
                    ->defaultValue(10)
                    ->min(0)
                ->end()
            ->end()
        ;

        return $node;
    }
    
    /**
     * Permet de configurer la partie ftp
     * @return TreeBuilder
     */
    public function addParametersNodeFTP()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('ftp');

        $node
            ->canBeEnabled()
            ->children()
                ->enumNode('rma_ftp')
                    ->values(array('yes', 'no'))
                    ->defaultValue('no')
                ->end()
                ->scalarNode('rma_ftp_ip')
                    ->defaultValue('127.0.0.1')
                ->end()
                ->scalarNode('rma_ftp_username')
                    ->defaultValue('rmausername')
                ->end()
                ->scalarNode('rma_ftp_password')
                    ->defaultValue('rmapassword')
                ->end()
                ->booleanNode('rma_ftp_port')
                    ->defaultValue('21')
                ->end()
                ->booleanNode('rma_ftp_timeout')
                    ->defaultValue('90')
                ->end()
                ->scalarNode('rma_ftp_path')
                    ->defaultValue('/home/rma/dump')
                ->end()
            ->end()
        ;
        return $node;
    }
    
    /**
     * Permet de configurer les classes d'accès
     * @return TreeBuilder
     */
    public function addParametersNodeClass()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('classes');

        $node
            ->children()
                ->scalarNode('rma_connexiondb')
                    ->defaultValue('RMA\Bundle\DumpBundle\ConnexionDB\ConnexionDB')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('rma_ftp')
                    ->defaultValue('RMA\Bundle\DumpBundle\Ftp\Rftp')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('rma_zip')
                    ->defaultValue('RMA\Bundle\DumpBundle\Zip\Rzip')
                    ->cannotBeEmpty()
                ->end()
                ->booleanNode('rma_dump')
                    ->defaultValue('RMA\Bundle\DumpBundle\Dump\RMADump')
                    ->cannotBeEmpty()
                ->end()
                ->booleanNode('rma_dump_mysql')
                    ->defaultValue('RMA\Bundle\DumpBundle\Dump\DumpMysql')
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;
        return $node;
    }
}
