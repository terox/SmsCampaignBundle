<?php

namespace Terox\SmsCampaignBundle\Service;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Terox\SmsCampaignBundle\Entity\Campaign;
use Terox\SmsCampaignBundle\Entity\Template;
use Terox\SmsCampaignBundle\Event\CampaignCreationEvent;

class CampaignFactory
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * CampaignFactory constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Create a new SMS Campaign.
     *
     * @param Template   $template
     * @param array      $context
     * @param Provider[] $providers
     *
     * @return Campaign
     */
    public function create(Template $template, $context = [], $providers = null)
    {
        $campaign = new Campaign();
        $campaign
            ->setTemplate($template)
            ->setContext($context);

        $campaignCreation = new CampaignCreationEvent($campaign, $providers);
        $this->eventDispatcher->dispatch('sms.campaign.creation', $campaignCreation);

        return $campaign;
    }
}