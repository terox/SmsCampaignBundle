<?php

namespace Terox\SmsCampaignBundle\Smpp;

use OnlineCity\Encoder\GsmEncoder;

use OnlineCity\SMPP\SmppAddress;
use OnlineCity\SMPP\SmppClient;
use OnlineCity\SMPP\SMPP;
use Terox\SmsCampaignBundle\Sms\Provider;
use Terox\SmsCampaignBundle\Sms\SmsTransmitterInterface;

class SmppTransmitter extends SmppBase implements SmsTransmitterInterface
{
    /**
     * {@inheritdoc}
     */
    protected function bindConnection(SmppClient $smppClient)
    {
        $smppClient->bindTransmitter($this->getLogin(), $this->getPassword());
    }

    /**
     * {@inheritdoc}
     */
    public function send($from, $to, $message)
    {
        $message = GsmEncoder::utf8_to_gsm0338($message);
        $from    = new SmppAddress($from, is_string($from) ? SMPP::TON_ALPHANUMERIC : SMPP::TON_INTERNATIONAL);
        $to      = new SmppAddress(intval($to), SMPP::TON_INTERNATIONAL, SMPP::NPI_E164);

        return $this->getClient()->sendSMS($from, $to, $message);
    }
}