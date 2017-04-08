<?php

namespace Terox\SmsCampaignBundle\Smpp;

use OnlineCity\SMPP\SmppClient;
use OnlineCity\SMPP\SMPP;
use OnlineCity\Transport\SocketTransport;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Terox\SmsCampaignBundle\Exception\RuntimeException;

abstract class SmppBaseAbstract
{
    /**
     * @var SmppClient
     */
    private $smppClient;

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $password;

    /**
     * @var boolean
     */
    private $isOpen;

    /**
     * Constructor.
     *
     * @param SmppClient $smppClient
     * @param string     $login
     * @param string     $password
     */
    public function __construct(SmppClient $smppClient, $login, $password)
    {
        $this->smppClient = $smppClient;
        $this->login      = $login;
        $this->password   = $password;
        $this->isOpen     = false;
    }

    /**
     * @return SmppClient
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
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Open connection to SMPP server.
     *
     * @return SmppBaseAbstract
     */
    public function openConnection()
    {
        if(!$this->isOpen()) {
            $this->getClient()->getTransport()->open();
            $this->bindConnection($this->smppClient);

            $this->isOpen = true;
        }

        return $this;
    }

    abstract protected function bindConnection(SmppClient $smppClient);

    /**
     * Close connection to SMPP server.
     *
     */
    public function closeConnection()
    {
        if(!$this->isOpen()) {
            return;
        }

        $this->getClient()->close();
        $this->isOpen = false;
    }

    /**
     * Is transport opened.
     *
     * @return bool
     */
    public function isOpen()
    {
        return $this->isOpen;
    }
}