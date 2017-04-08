<?php

namespace Terox\SmsCampaignBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Terox\SmsCampaignBundle\Entity\Campaign;
use Terox\SmsCampaignBundle\Entity\Provider;

class CampaignCreationEvent extends Event
{
    /**
     * @var Campaign
     */
    private $campaign;

    /**
     * @var null|Provider[]
     */
    private $providers;

    /**
     * CampaignCreationEvent constructor.
     *
     * @param Campaign         $campaign
     * @param null|Providers[] $providers
     */
    public function __construct(Campaign $campaign, $providers = null)
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