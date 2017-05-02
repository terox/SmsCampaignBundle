<?php

namespace Terox\SmsCampaignBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Terox\SmsCampaignBundle\DependencyInjection\TeroxSmsCampaignExtension;
use Terox\SmsCampaignBundle\Entity\Message;
use Terox\SmsCampaignBundle\Entity\MessageState;
use OnlineCity\SMPP\Unit\SmppDeliveryReceipt;

class UpdateStatusCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sms:dlr:update')
            ->setDescription('Update SMS status')
            ->addArgument(
                'message',
                InputArgument::REQUIRED,
                'JSON encoded DLR',
                null
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dlr                = json_decode($input->getArgument('message'), true);
        $entityManager      = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $messageRepository  = $this->getContainer()->get('sms.repository.message');

        $messageId  = $dlr['id'];
        $stat       = $dlr['stat'];
        $submitDate = new \DateTime($dlr['submitDate']);
        $doneDate   = new \DateTime($dlr['doneDate']);

        /** @var Message|null $message */
        $message = $messageRepository->findOneBy([ 'messageId' => $messageId ]);

        if(null === $message) {
            return $output->writeln(sprintf('Message not found: %s', $messageId));
        }

        $state = new MessageState();
        $state->setProviderStatus($stat);

        $message
            ->setSubmitDate($submitDate)
            ->setDoneDate($doneDate)
            ->addState($state);

        $entityManager->persist($message);
        $entityManager->flush();

        $output->writeln('<fg=green>Success!</>');
    }
}