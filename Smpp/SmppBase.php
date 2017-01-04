<?php

namespace Terox\SmsCampaignBundle\Smpp;

use OnlineCity\SMPP\SmppClient;
use OnlineCity\SMPP\SMPP;
use OnlineCity\Transport\SocketTransport;

abstract class SmppBase
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
     * @var integer
     */
    private $timeout;

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $password;

    /**
     * @var null|SocketTransport
     */
    private $transport;

    /**
     * @var null|SmppClient
     */
    private $smppClient;

    /**
     * @var boolean
     */
    private $isOpen;

    /**
     * SmppTransmitter constructor.
     *
     * @param string $host
     * @param integer $port
     * @param integer $timeout
     * @param string $login
     * @param string $password
     */
    public function __construct($host, $port, $timeout, $login, $password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $this->login = $login;
        $this->password = $password;
        $this->isOpen = false;
    }

    /**
     * @return null|SmppClient
     */
    public function getClient()
    {
        return $this->smppClient;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param string $login
     *
     * @return SmppBase
     */
    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return SmppBase
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Open connection to SMPP server.
     *
     */
    public function openConnection()
    {
        $this->transport = new SocketTransport([$this->host], $this->port);
        $this->transport->setSendTimeout($this->timeout);

        $this->smppClient = new SmppClient($this->transport);

        $this->transport->open();
        $this->bindConnection($this->smppClient);

        $this->isOpen = true;
    }

    abstract protected function bindConnection(SmppClient $smppClient);

    /**
     * Close connection to SMPP server.
     *
     */
    public function closeConnection()
    {
        $this->smppClient->close();
        $this->isOpen = false;
    }

    public function isOpen()
    {
        return $this->isOpen;
    }
}