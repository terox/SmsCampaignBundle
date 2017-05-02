<?php

namespace Terox\SmsCampaignBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Terox\SmsCampaignBundle\Smpp\SmppTransmitter;
use Terox\SmsCampaignBundle\TeroxSmsCampaignBundle;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class TeroxSmsCampaignExtension extends Extension implements PrependExtensionInterface
{
    const NS_TRANSMITTER = TeroxSmsCampaignBundle::BUNDLE_NAMESPACE.'.smpp-transmitter';

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter(TeroxSmsCampaignBundle::BUNDLE_NAMESPACE.'.config', $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('services.job.yml');
        $loader->load('services.repository.yml');

        $this->createDependencyInjectionServices($container, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        if(isset($bundles['OldSoundRabbitMqBundle'])) {
            $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('rabbitmq.yml');
        }
    }

    protected function createDependencyInjectionServices(ContainerBuilder $container, $config)
    {
        // Create SMPP Transmitters
        foreach($config['providers'] as $providerCodename => $parameters) {
            $container
                ->register(
                    self::NS_TRANSMITTER.'.'.$providerCodename,
                    SmppTransmitter::class
                )
                ->addArgument($parameters['rpc']['host'])
                ->addArgument($parameters['rpc']['port']);
        }
    }
}