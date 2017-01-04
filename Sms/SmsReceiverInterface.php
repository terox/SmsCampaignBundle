<?php

namespace Terox\SmsCampaignBundle\Sms;

use OnlineCity\SMPP\Unit\SmppDeliveryReceipt;

interface SmsReceiverInterface
{
    /**
     * Receive receipts from sent SMS.
     *
     * @return SmppDeliveryReceipt[]
     */
    public function receipts();
}