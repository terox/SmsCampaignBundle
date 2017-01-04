<?php

namespace Terox\SmsCampaignBundle\Job\Consumer;

use Monolog\Logger;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Terox\SmsCampaignBundle\Entity\Message;
use Terox\SmsCampaignBundle\Entity\Provider;
use Terox\SmsCampaignBundle\Service\SmppTransmitterFactory;

class SmsMessageConsumer implements ConsumerInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var
     */
    private $messageRepository;

    /**
     * @var SmppTransmitterFactory
     */
    private $smppTransmitterFactory;

    /**
     * @var array
     */
    private $connections;

    /**
     * SmsConsumer constructor.
     *
     * @param Logger                   $logger
     * @param EntityManager            $em
     * @param $messageRepository
     * @param SmppTransmitterFactory   $smppTransmitterFactory
     */
    public function __construct(
        Logger $logger,
        EntityManager $em,
        $messageRepository,
        SmppTransmitterFactory $smppTransmitterFactory
    )
    {
        $this->logger                 = $logger;
        $this->entityManager          = $em;
        $this->messageRepository      = $messageRepository;
        $this->smppTransmitterFactory = $smppTransmitterFactory;
    }

    /**
     * @param AMQPMessage $msg The message
     *
     * @return mixed false to reject and requeue, any other value to acknowledge
     */
    public function execute(AMQPMessage $msg)
    {
        // Queue message params
        $body      = (object)unserialize($msg->body);
        $messageId = $body->messageId;
        $content   = $body->content;

        // Entities
        $message  = $this->messageRepository->findOneById($messageId);
        $provider = $message->getProvider();
        $campaign = $message->getCampaign();
        $template = $campaign->getTemplate();

        $this->logger->info('Preparing message with ID: {id} to send through `{providerName}` with route `{route}`', [
            'message'      => $message->getId(),
            'providerName' => $provider->getName(),
            'route'        => $provider->getRoute()
        ]);

        if(null === $campaign) {
            $this->logger->info('Message has not any campaign assigned');

            // TODO campaign doesn't exist, individual message
            return;
        }

        $messageId = $this
            ->getTransmitter($provider)
            ->send($template->getSender(), $message->getPhoneNumber(), $content)
        ;

        $message
            ->setMessageId($messageId)
            ->setStatus(Message::STATUS_SENT)
        ;

        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }

    private function getTransmitter(Provider $provider)
    {
        if(!isset($this->connections[$provider->getCode()])) {
            $this->connections[$provider->getCode()] = $this->smppTransmitterFactory->create($provider);
            $this->connections[$provider->getCode()]->openConnection();
        }

        return $this->connections[$provider->getCode()];
    }

    /**
     * Close all connections.
     */
    public function __destruct()
    {
        foreach($this->connections as $connection) {
            if($connection->isOpen()) {
                $connection->closeConnection();
            }
        }
    }
}