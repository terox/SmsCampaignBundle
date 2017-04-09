<?php

namespace Terox\SmsCampaignBundle\Smpp;

use OnlineCity\SMPP\Unit\SmppDeliveryReceipt;

class ReceiverIterator implements \Iterator
{
    /**
     * @var SmppReceiver
     */
    private $smppReceiver;

    /**
     * @var int
     */
    private $position;

    /**
     * @var null|SmppDeliveryReceipt
     */
    private $current;

    /**
     * Constructor.
     *
     * @param SmppReceiver $smppReceiver
     */
    public function __construct(SmppReceiver $smppReceiver)
    {
        $this->position     = 0;
        $this->current      = null;
        $this->smppReceiver = $smppReceiver;
    }

    /**
     * @return \OnlineCity\SMPP\SmppClient
     */
    private function readSMS()
    {
        return $this->current = $this->smppReceiver->getClient()->readSMS();
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        if(null === $this->current) {
            $this->readSMS();
        }

        return $this->current;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->readSMS();
        $this->position++;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return false !== $this->current;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {

    }
}