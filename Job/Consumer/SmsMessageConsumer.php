<?php

namespace Terox\SmsCampaignBundle\Job\Consumer;

use Monolog\Logger;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OnlineCity\SMPP\SMPP;
use OnlineCity\SMPP\SmppClient;
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
     * @var null|\DateTime
     */
    private $lastTransmission;

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
        $this->connections            = [];
        $this->lastTransmission       = null;
    }

    /**
     * @param AMQPMessage $msg The message
     *
     * @return mixed false to reject and requeue, any other value to acknowledge
     */
    public function execute(AMQPMessage $msg)
    {
        // Queue message params
        $body       = (object)unserialize($msg->body);
        $messageId  = $body->messageId;
        $content    = $body->content;

        // Entities
        $message  = $this->messageRepository->findOneById($messageId);
        $provider = $message->getProvider();
        $campaign = $message->getCampaign();
        $template = $campaign->getTemplate();

        // If message have 3 or more attempts of sent, discard
        if($message->getAttempts() >= 3) {

            $this->logger->error('Message with ID: {id} was discarded', [
                'id' => $message->getId()
            ]);

            $this->saveMessage(Message::STATUS_DISCARDED);

            print sprintf('Discarded SMS: %s', $message->getId());

            return;
        }

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

        try {

            print sprintf('Sending SMS: %s', $message->getId());

            $messageId = $this
                ->getTransmitter($provider)
                ->send($template->getSender(), $message->getPhoneNumber(), $content)
            ;

            $message
                ->setMessageId($messageId)
                ->setStatus(Message::STATUS_SENT)
            ;

            $this->saveMessage($message);
            $this->lastTransmission = time();

        } catch(\Exception $exception) {

            $timestamp      = $this->lastTransmission;
            $stringDateTime = null !== $timestamp ? date('Y-m-d H:i:s', $timestamp) : '(no transmission)';

            print sprintf('Exception. Maybe connection was lost? Last transmission: %s', $stringDateTime);

            $this->logger->info('Something went wrong sending the SMS ({id}): {message}. Maybe the connection with SMPP was lost. The SMS ({id}) will be requeued', [
                'id'      => $message->getId(),
                'message' => $exception->getMessage()
            ]);

            $this->logger->info('Last transmission was at {datetime}', [
                'datetime' =>$stringDateTime
            ]);

            // Reconnect with the provider
            $this->reconnect($provider);

            // Change status and increase attempt
            $message->setStatus(Message::STATUS_REQUEUED)->increaseAttempt();
            $this->saveMessage($message);

            // Requeue message
            return false;
        }
    }

    /**
     * Get transmitter service.
     *
     * @param Provider $provider
     *
     * @return mixed
     */
    private function getTransmitter(Provider $provider)
    {
        if(!isset($this->connections[$provider->getCode()])) {
            $this->connections[$provider->getCode()] = $this->smppTransmitterFactory->create($provider);
            $this->connections[$provider->getCode()]->openConnection();
        }

        return $this->connections[$provider->getCode()];
    }

    /**
     * Reconnect with SMPP provider.
     *
     * @param Provider $provider
     */
    private function reconnect(Provider $provider)
    {
        $this->connections[$provider->getCode()]->openConnection();
    }

    /**
     * Save message and clean.
     *
     * @param Message $message
     */
    private function saveMessage(Message $message)
    {
        $this->entityManager->persist($message);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * Destroy consumer:
     * - Destroy opened SMPP connections.
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