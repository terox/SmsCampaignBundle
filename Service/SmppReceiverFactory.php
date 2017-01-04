<?php

namespace Terox\SmsCampaignBundle\Service;

use Terox\SmsCampaignBundle\Entity\Provider;
use Terox\SmsCampaignBundle\Smpp\SmppReceiver;

class SmppReceiverFactory
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

    public function create(Provider $provider)
    {
        if(!isset($this->providers[$provider->getCode()])) {
            throw new ProviderNotFoundException();
        }

        $config = (object)$this->providers[$provider->getCode()];

        $transmitter = new SmppReceiver(
            $config->host,
            $config->port,
            $config->timeout,
            $config->login,
            $config->password
        );

        return $transmitter;
    }
}