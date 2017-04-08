<?php

namespace Terox\SmsCampaignBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Terox\SmsCampaignBundle\TeroxSmsCampaignBundle;
use OnlineCity\SMPP\SmppClient;

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
                            ->intNode('timeout_sender')->cannotBeEmpty()->defaultValue(10000)->end()     // 10 secs
                            ->intNode('timeout_receiver')->cannotBeEmpty()->defaultValue(300000)->end()  // 5 mins
                            ->arrayNode('options')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->booleanNode('smsNullTerminateOctetstrings')->defaultFalse()->end()
                                    ->scalarNode('csmsMethod')->defaultValue(SmppClient::CSMS_PAYLOAD)->end()
                                    ->scalarNode('smsRegisteredDeliveryFlag')->defaultValue(0x00)->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('debug')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('transport')->defaultFalse()->end()
                        ->booleanNode('smpp')->defaultFalse()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}