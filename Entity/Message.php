<?php

namespace Terox\SmsCampaignBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ekino\WordpressBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="SmsMessages",
 *     indexes={
 *         @ORM\Index(name="IDX_message_id", columns={"message_id"}),
 *         @ORM\Index(name="IDX_phone_number", columns={"phone_number"})
 *     }
 * )
 */
class Message
{
    const STATUS_PENDING   = 0;
    const STATUS_ERROR     = 1;
    const STATUS_SENT      = 2;
    const STATUS_DELIVERED = 3;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var integer
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Provider")
     * @ORM\JoinColumn(name="provider_id", referencedColumnName="id", nullable=false)
     *
     * @var Provider
     */
    private $provider;

    /**
     * @ORM\ManyToOne(targetEntity="Campaign")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     *
     * @var Campaign
     */
    private $campaign;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $messageId;

    /**
     * @ORM\Column(type="integer", length=2, options={"unsigned":true})
     *
     * @var integer
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="MessageState", mappedBy="message", cascade={"persist", "remove"})
     *
     * @var ArrayCollection
     */
    private $states;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $submitDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $doneDate;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="create")
     *
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="update")
     *
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * Message constructor.
     */
    public function __construct()
    {
        $this->status = self::STATUS_PENDING;
        $this->states = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param MessageState $state
     * @return $this
     */
    public function addState(MessageState $state)
    {
        $state->setMessage($this);
        $this->states->add($state);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * @return Provider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param Provider $provider
     *
     * @return Message
     */
    public function setProvider(Provider $provider)
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * @return Provider
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @param Provider $campaign
     *
     * @return Message
     */
    public function setCampaign($campaign)
    {
        $this->campaign = $campaign;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     *
     * @return Message
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @param string $messageId
     *
     * @return Message
     */
    public function setMessageId($messageId)
    {
        $this->messageId = $messageId;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return Message
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubmitDate()
    {
        return $this->submitDate;
    }

    /**
     * @param mixed $submitDate
     *
     * @return Message
     */
    public function setSubmitDate(\DateTime $submitDate)
    {
        $this->submitDate = $submitDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDoneDate()
    {
        return $this->doneDate;
    }

    /**
     * @param \DateTime $doneDate
     *
     * @return Message
     */
    public function setDoneDate(\DateTime $doneDate)
    {
        $this->doneDate = $doneDate;
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
     * @return Message
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return Message
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}