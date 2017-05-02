<?php

namespace Terox\SmsCampaignBundle\Smpp;

use Terox\SmsCampaignBundle\Exception\RuntimeException;

abstract class SmppBaseAbstract
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var integer
     */
    private $port;

    /**
     * @var React\EventLoop\StreamSelectLoop
     */
    protected $loop;

    /**
     * @var int
     */
    protected $dnode;

    /**
     * Constructor.
     *
     * @param string  $host
     * @param integer $port
     */
    public function __construct($host, $port)
    {
        $this->host  = $host;
        $this->port  = $port;
        $this->loop  = new \React\EventLoop\StreamSelectLoop();
        $this->dnode = new \DNode\DNode($this->loop);
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }
}