<?php

namespace Terox\SmsCampaignBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Terox\SmsCampaignBundle\TeroxSmsCampaignBundle;


/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(TeroxSmsCampaignBundle::BUNDLE_NAMESPACE);
        $rootNode
            ->children()
                ->arrayNode('providers')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('port')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('login')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
                            ->arrayNode('rpc')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('exec')->defaultValue('/usr/bin/smpp-cli')->end()
                                    ->scalarNode('host')->defaultValue('127.0.0.1')->end()
                                    ->integerNode('port')->defaultValue(7070)->end()
                                    ->scalarNode('dlr')->defaultValue(null)->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}