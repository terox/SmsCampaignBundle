<?php

namespace Terox\SmsCampaignBundle\Service;

use OnlineCity\SMPP\SMPP;
use OnlineCity\SMPP\SmppClient;
use Terox\SmsCampaignBundle\Entity\Provider;
use Terox\SmsCampaignBundle\Exception\ProviderNotFoundException;
use Terox\SmsCampaignBundle\Smpp\SmppTransmitter;

class SmppTransmitterFactory
{
    /**
     * @var array
     */
    protected $providers;

    /**
     * SmppTransmitterFactory constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->providers = $config['providers'];
    }

    /**
     * Create SmppTransmitter.
     *
     * @param Provider $provider
     *
     * @return SmppTransmitter
     *
     * @throws ProviderNotFoundException
     */
    public function create(Provider $provider)
    {
        if(!isset($this->providers[$provider->getCode()])) {
            throw new ProviderNotFoundException();
        }

        $config = (object)$this->providers[$provider->getCode()];

        $transmitter = new SmppTransmitter(
            $config->host,
            $config->port,
            $config->timeout,
            $config->login,
            $config->password
        );

        return $transmitter;
    }
}