<?php

namespace Terox\SmsCampaignBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Terox\SmsCampaignBundle\Entity\Message;
use Terox\SmsCampaignBundle\Entity\MessageState;

class UpdateStatusCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sms:status:update')
            ->setDescription('Update SMS status')
            ->addOption(
                'provider',
                'p',
                InputOption::VALUE_REQUIRED,
                'Name of provider(s) separated by commas where send the messages',
                'default'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $providerCode       = $input->getOption('provider');
        $entityManager      = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $messageRepository  = $this->getContainer()->get('sms.repository.message');
        $providerRepository = $this->getContainer()->get('sms.repository.provider');
        $receiverFactory    = $this->getContainer()->get('sms.smpp.receiver.factory');

        $provider = $providerRepository->findOneByCode($providerCode);
        $receiver = $receiverFactory->create($provider);

        $output->writeln('<fg=yellow>Connecting...</>');
        $receiver->openConnection();
        $receipts = $receiver->receipts();

        if(0 === count($receipts)) {
            $output->writeln('<fg=blue>> No receipts available</>');
        } else {

            foreach($receipts as $receipt)
            {
                $message = $messageRepository->findOneByMessageId($receipt->id);

                if(null === $message || Message::STATUS_DELIVERED === $message->getStatus()) {
                    continue;
                }

                $statusMsg  = $receipt->stat;
                $submitDate = (new \DateTime())->setTimestamp($receipt->submitDate);
                $doneDate   = (new \DateTime())->setTimestamp($receipt->doneDate);

                $state = new MessageState();
                $state->setProviderStatus($statusMsg);

                $message
                    ->setSubmitDate($submitDate)
                    ->setDoneDate($doneDate)
                    ->addState($state)
                ;

                $output->writeln(
                    sprintf('<fg=green>> Received confirmation from</> <fg=yellow>%s</> (<fg=white>%s</>)',
                        $message->getPhoneNumber(),
                        $message->getMessageId()
                    )
                );

                $entityManager->persist($message);
            }

        }
        $receiver->closeConnection();
        $output->writeln('<fg=yellow>Close connection.</>');

        $entityManager->flush();
    }
}