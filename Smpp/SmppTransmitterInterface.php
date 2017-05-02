<?php

namespace Terox\SmsCampaignBundle\Smpp;

interface SmppTransmitterInterface
{
    /**
     * Send SMS.
     *
     * @param string   $from
     * @param string   $to
     * @param string   $message
     * @param callable $callback
     *
     * @return SmppTransmitterInterface
     */
    public function send($from, $to, $message, callable $callback);
}