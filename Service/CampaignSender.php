<?php

namespace Terox\SmsCampaignBundle\Service;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Terox\SmsCampaignBundle\Entity\Campaign;
use Terox\SmsCampaignBundle\Entity\Message;
use Terox\SmsCampaignBundle\Job\Producer\SmsMessageProducer;
use Terox\SmsCampaignBundle\Event\MessageContentEvent;

class CampaignSender
{
    /**
     * @var SmsMessageProducer
     */
    private $smsProducer;

    /**
     * @var
     */
    private $contextReplacer;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * CampaignManager constructor.
     *
     * @param SmsMessageProducer $smsProducer
     */
    public function __construct(SmsMessageProducer $smsProducer, $contextReplacer, EventDispatcherInterface $eventDispatcher)
    {
        $this->smsProducer     = $smsProducer;
        $this->contextReplacer = $contextReplacer;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Send campaign.
     *
     * @param Campaign $campaign
     * @param array    $messageStatus
     */
    public function send(Campaign $campaign, $messageStatus = [Message::STATUS_PENDING])
    {
        foreach($campaign->getMessages() as $message) {
            if(in_array($message->getStatus(), $messageStatus, true)) {

                $context         = $campaign->getContext();
                $templateContent = $message->getCampaign()->getTemplate()->getContent();
                var_dump();
                $templateContent = $this->contextReplacer->replace($templateContent, $context);
                $messageContent  = new MessageContentEvent($templateContent);

                $this->eventDispatcher->dispatch('sms.campaign.message.sent', $messageContent);

                $this->smsProducer->publish($message, (string)$messageContent);
            }
        }
    }

    /**
     * Send messages that aren't sent in campaign.
     *
     * @param Campaign $campaign
     */
    public function forwardWithErrors(Campaign $campaign)
    {
        $this->send($campaign, [
            Message::STATUS_PENDING,
            Message::STATUS_ERROR
        ]);
    }

    /**
     * Send pending messages in campaign.
     *
     * @param Campaign $campaign
     */
    public function forwardPending(Campaign $campaign)
    {
        $this->send($campaign, [
            Message::STATUS_PENDING
        ]);
    }
}