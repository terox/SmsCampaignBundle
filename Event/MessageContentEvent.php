<?php

namespace Terox\SmsCampaignBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class MessageContentEvent extends Event
{
    /**
     * @var string
     */
    private $content;

    /**
     * MessageContent constructor.
     *
     * @param $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getContent();
    }
}