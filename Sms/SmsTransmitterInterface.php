<?php

namespace Terox\SmsCampaignBundle\Sms;

interface SmsTransmitterInterface
{
    /**
     * Send SMS.
     *
     * @param string $from
     * @param string $to
     * @param string $message
     *
     * @return string SMS Message ID
     */
    public function send($from, $to, $message);
}