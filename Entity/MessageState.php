<?php

namespace Terox\SmsCampaignBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="SmsMessageStates")
 */
class MessageState
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var integer
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Message", inversedBy="status")
     * @ORM\JoinColumn(name="message_id", referencedColumnName="id", nullable=false)
     *
     * @var Message
     */
    private $message;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $providerStatus;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     *
     * @var \DateTime
     */
    private $createdAt;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param Message $message
     *
     * @return MessageStatus
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->providerStatus;
    }

    /**
     * @param int $status
     *
     * @return MessageStatus
     */
    public function setStatus($status)
    {
        $this->providerStatus = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getProviderStatus()
    {
        return $this->providerStatus;
    }

    /**
     * @param string $providerStatus
     *
     * @return MessageStatus
     */
    public function setProviderStatus($providerStatus)
    {
        $this->providerStatus = $providerStatus;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return MessageStatus
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}