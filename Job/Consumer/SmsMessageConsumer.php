<?php

namespace Terox\SmsCampaignBundle\Job\Consumer;

use Monolog\Logger;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Terox\SmsCampaignBundle\DependencyInjection\TeroxSmsCampaignExtension;
use Terox\SmsCampaignBundle\Entity\Message;
use Terox\SmsCampaignBundle\Entity\Provider;
use Terox\SmsCampaignBundle\Smpp\SmppTransmitter;

class SmsMessageConsumer implements ConsumerInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

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
     * @param  $messageRepository
     */
    public function __construct(
        Logger $logger,
        EntityManager $em,
        $messageRepository
    )
    {
        $this->logger                 = $logger;
        $this->entityManager          = $em;
        $this->messageRepository      = $messageRepository;
        $this->connections            = [];
        $this->lastTransmission       = null;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param AMQPMessage $msg The message
     *
     * @return mixed false to reject and requeue, any other value to acknowledge
     */
    public function execute(AMQPMessage $msg)
    {
        // First checks
        $this->checkDatabaseConnection();

        // Queue message params
        $body       = (object)unserialize($msg->body);
        $messageId  = $body->messageId;
        $content    = $body->content;

        // Entities
        $message  = $this->messageRepository->findOneById($messageId);
        $provider = $message->getProvider();
        $campaign = $message->getCampaign();
        $template = $campaign->getTemplate();

        // SMPP
        $transmitter = $this->getTransmitter($provider);

        // If message have 3 or more attempts of sent, discard
        if($message->getAttempts() >= 3) {

            $this->logger->error('Message with ID: {id} was discarded', [
                'id' => $message->getId()
            ]);

            $message->setStatus(Message::STATUS_DISCARDED);
            $this->saveMessage($message);

            print sprintf("Discarded SMS: %s\n", $message->getId());

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

            print sprintf("SMS: %s sending...\n", $message->getId());

            $messageId = $transmitter->send($template->getSender(), $message->getPhoneNumber(), $content);

            $message->setMessageId($messageId)->setStatus(Message::STATUS_SENT);
            $this->saveMessage($message);

            $this->lastTransmission = time();

            print sprintf("SMS: %s sent successfully\n", $message->getId());

        } catch(\Exception $exception) {

            $timestamp      = $this->lastTransmission;
            $stringDateTime = null !== $timestamp ? date('Y-m-d H:i:s', $timestamp) : '(no transmission)';

            print sprintf("Exception. Maybe connection was lost? Last transmission: %s\n", $stringDateTime);

            $this->logger->info(
                'Something went wrong sending the SMS ({id}): {message}. Maybe the connection with SMPP was lost. 
                The SMS ({id}) will be requeued. Last transmission was at {datetime}',
                [
                    'id'       => $message->getId(),
                    'message'  => $exception->getMessage(),
                    'datetime' => $stringDateTime
                ]
            );

            // Change status and increase attempt
            $message->setStatus(Message::STATUS_REQUEUED)->increaseAttempt();
            $this->saveMessage($message);

            // Reset connection
            $transmitter->closeConnection();
            $transmitter->openConnection();

            // Requeue message
            return false;
        }
    }

    /**
     * Check database connection and reconnect in case of fail.
     *
     * For long running process doctrine may be throw a "MySQL gone away" or similar exception depending on your RDBMS
     * or your server configuration. This snippet check the connection for current Entity Manager and reconnects if it
     * was lost.
     *
     * Keep in mind that it don't prevent other type of connectivity issues.
     *
     * @see http://stackoverflow.com/a/26791224/2204237 (thank you!)
     */
    private function checkDatabaseConnection()
    {
        $em = $this->entityManager;

        if ($em->getConnection()->ping() === false) {
            $em->getConnection()->close();
            $em->getConnection()->connect();
        }
    }

    /**
     * Get transmitter service for provider.
     *
     * @param Provider $provider
     *
     * @return SmppTransmitter
     */
    private function getTransmitter(Provider $provider)
    {
        if(!in_array($provider->getCode(), $this->connections)) {
            $this->connections[] = $provider->getCode();
        }

        return $this->container->get(TeroxSmsCampaignExtension::NS_TRANSMITTER.'.'.$provider->getCode());
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