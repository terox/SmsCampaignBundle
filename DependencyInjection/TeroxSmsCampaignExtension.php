<?php

namespace Terox\SmsCampaignBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Terox\SmsCampaignBundle\Smpp\SmppReceiver;
use Terox\SmsCampaignBundle\Smpp\SmppTransmitter;
use Terox\SmsCampaignBundle\TeroxSmsCampaignBundle;
use OnlineCity\SMPP\SmppClient;
use OnlineCity\Transport\SocketTransport;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class TeroxSmsCampaignExtension extends Extension implements PrependExtensionInterface
{
    const NS_SOCKET_TRANSPORT = TeroxSmsCampaignBundle::BUNDLE_NAMESPACE.'.transport';
    const NS_SMPP_CLIENT      = TeroxSmsCampaignBundle::BUNDLE_NAMESPACE.'.smpp-client';
    const NS_TRANSMITTER      = TeroxSmsCampaignBundle::BUNDLE_NAMESPACE.'.smpp-transmitter';
    const NS_RECEIVER         = TeroxSmsCampaignBundle::BUNDLE_NAMESPACE.'.smpp-receiver';

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
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
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
        foreach($config['providers'] as $providerCodename => $parameters) {
            $nsSocketTransport = self::NS_SOCKET_TRANSPORT.'.'.$providerCodename;
            $nsSmppClient      = self::NS_SMPP_CLIENT.'.'.$providerCodename;

            // Create SocketTransport services
            $container
                ->register($nsSocketTransport, SocketTransport::class)
                ->addArgument([$parameters['host']])
                ->addArgument($parameters['port'])
                ->addArgument(false)
                ->addArgument($config['debug']['transport'])
                ->addMethodCall('setSendTimeout', [$parameters['timeout_sender']])
                ->addMethodCall('setRecvTimeout', [$parameters['timeout_receiver']]);

            // Create SMPPClient services
            $container
                ->register($nsSmppClient, SmppClient::class)
                ->addArgument(new Reference($nsSocketTransport))
                ->addArgument($parameters['options'])
                ->addArgument($config['debug']['smpp']);

            // Create SMPP Transmitters
            $container
                ->register(self::NS_TRANSMITTER.'.'.$providerCodename, SmppTransmitter::class)
                ->addArgument(new Reference($nsSmppClient))
                ->addArgument($parameters['login'])
                ->addArgument($parameters['password']);

            // Create SMPP Receivers
            $container
                ->register(self::NS_RECEIVER.'.'.$providerCodename, SmppReceiver::class)
                ->addArgument(new Reference($nsSmppClient))
                ->addArgument($parameters['login'])
                ->addArgument($parameters['password']);
        }
    }
}