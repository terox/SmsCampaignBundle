<?php

namespace Terox\SmsCampaignBundle\Smpp;

use OnlineCity\SMPP\SmppClient;
use Terox\SmsCampaignBundle\Sms\SmsReceiverInterface;

class SmppReceiver extends SmppBase implements SmsReceiverInterface
{
    /**
     * {@inheritdoc}
     */
    protected function bindConnection(SmppClient $smppClient)
    {
        $smppClient->bindReceiver($this->getLogin(), $this->getPassword());
    }

    /**
     * Receive receipts from sent SMS.
     *
     * @return mixed
     */
    public function receipts()
    {
        $receipts = [];
        while($receipt = $this->getClient()->readSMS()) {
            $receipts[] = $receipt;
        }

        return $receipts;
    }
}