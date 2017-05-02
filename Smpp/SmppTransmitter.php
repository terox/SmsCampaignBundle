<?php

namespace Terox\SmsCampaignBundle\Smpp;

use Terox\SmsCampaignBundle\Sms\Provider;
use Terox\SmsCampaignBundle\Smpp\SmsTransmitterInterface;

class SmppTransmitter extends SmppBaseAbstract implements SmppTransmitterInterface
{
    /**
     * {@inheritdoc}
     */
    public function send($from, $to, $message, callable $callback)
    {
        $host = $this->getHost();
        $port = $this->getPort();

        $this->dnode->connect($host, $port, function($remote, $connection) use ($from, $to, $message, $callback) {

            $remote->send([
                'sourceAddress'      => $from,
                'destinationAddress' => $to,
                'message'            => $message
            ], function($messageId) use ($connection, $callback) {
                $callback($messageId);
                $connection->end();
            });

        });

        $this->loop->run();
    }
}