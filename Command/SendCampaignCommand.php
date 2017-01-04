<?php

namespace Terox\SmsCampaignBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class SendCampaignCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sms:campaign:send')
            ->setDescription('Launch providers endpoints to queues')
            ->addOption(
                'campaign',
                'c',
                InputOption::VALUE_REQUIRED,
                'Campaign ID',
                null
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $campaignId         = $this->getOption('campaign');
        $campaignRepository = $this->getContainer()->get('sms.repository.campaign');
        $campaign           = $campaignRepository->findOne($campaignId);

        if(null !== $campaign) {

            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                sprintf(
                    '<fg=red>Do you want send the campaign %s?</> <fg=green>You are going to send %s SMS(s)</>',
                    $campaign->getName(),
                    $campaign->getNumberOfMessages()
                ),
                false
            );

            if ($helper->ask($input, $output, $question)) {
                $output->writeln('<fg=green>Campaign queued!</>');
                $this->getContainer()->get('sms.campaign.sender')->send($campaign);
            } else {
                $output->writeln('<fg=yellow>Action cancelled</>');
            }

            return;
        }

        $output->writeln(
            sprintf('<fg=red>Campaign with ID %s. does not exists</>', $campaignId)
        );
    }
}