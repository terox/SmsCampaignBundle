<?php

namespace Terox\SmsCampaignBundle\Job\Producer;

use Terox\SmsCampaignBundle\Entity\Message;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

class SmsMessageProducer
{
    /**
     * @var Producer
     */
    private $producer;

    /**
     * SmsProducer constructor.
     *
     * @param Producer $producer
     */
    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
    }

    /**
     * @param Message $message
     * @param string  $content
     */
    public function publish(Message $message, $content)
    {
        $this->producer->publish(serialize([
            'messageId' => $message->getId(),
            'content'   => $content
        ]));
    }
}