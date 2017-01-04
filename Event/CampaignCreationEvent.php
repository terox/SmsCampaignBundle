<?php

namespace Terox\SmsCampaignBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Terox\SmsCampaignBundle\Entity\Provider;

class CampaignCreationEvent extends Event
{
    /**
     * @var Campaign
     */
    private $campaign;

    /**
     * @var Provider[]
     */
    private $providers;

    /**
     * CampaignCreationEvent constructor.
     *
     * @param $campaign
     * @param $providers
     */
    public function __construct($campaign, $providers)
    {
        $this->campaign  = $campaign;
        $this->providers = $providers;
    }

    /**
     * @return Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @return Provider[]
     */
    public function getProviders()
    {
        return $this->providers;
    }
}