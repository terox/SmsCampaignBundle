<?php

namespace Terox\SmsCampaignBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Terox\SmsCampaignBundle\Entity\Campaign;
use Terox\SmsCampaignBundle\Exception\NoProvidersException;
use Terox\SmsCampaignBundle\Exception\NoUsersException;

class CreateCampaignCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sms:campaign:create')
            ->setDescription('Create a new SMS campaign')
            ->addOption(
                'template',
                't',
                InputOption::VALUE_REQUIRED,
                'Template to use in this campaign',
                null
            )
            ->addOption(
                'context',
                'C',
                InputOption::VALUE_OPTIONAL,
                'Context variables: varName=value1,varName2=value2...',
                null
            )
            ->addOption(
                'provider',
                'p',
                InputOption::VALUE_REQUIRED,
                'Name of provider(s) separated by commas where send the messages (messages will be balanced between providers)',
                'default'
            )
            ->addOption(
                'send',
                'S',
                InputOption::VALUE_NONE,
                'Send campaign. Default: not send'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force campaign send'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Command options
        $templateName = strtolower($input->getOption('template'));
        $context      = $input->getOption('context');
        $providers    = $input->getOption('provider');
        $sendCampaign = $input->getOption('send');
        $sendForce    = $input->getOption('force');

        // Services
        $entityManager   = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $campaignFactory = $this->getContainer()->get('sms.campaign.factory');
        $contextCreator  = $this->getContainer()->get('sms.context.creator');

        // Repositories
        $providerRepository = $this->getContainer()->get('sms.repository.provider');
        $templateRepository = $this->getContainer()->get('sms.repository.template');

        // Prepare and create campaign
        $template = $templateRepository->findOneByName($templateName);

        if(null === $template) {
            $output->writeln(sprintf('<fg=red>The template with name %s doesn\'t exists</>', $templateName));
            return;
        }

        if(null === $providers) {
            $output->writeln('<fg=red>At least one provider must be provided</>');
            return;
        }

        $providers = array_map(function($name) use ($providerRepository) {
            return $providerRepository->findOneByCode(trim(strtolower($name)));
        }, explode(',', $providers));

        $campaign = $campaignFactory->create(
            $template,
            $providers,
            $contextCreator->fromString($context)
        );

        $entityManager->persist($campaign);
        $entityManager->flush();

        // Force campaign send
        if(true === $sendCampaign && $sendForce) {
            return $this->sendCampaign($campaign);
        }

        // Send campaign with confirmation
        if(true === $sendCampaign) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                sprintf(
                    '<fg=red>Do you want send the campaign?</> <fg=green>You are going to send %s SMS with %s</>',
                    $campaign->getNumberOfMessages(),
                    implode(',', $providers)
                ),
                false
            );

            if($helper->ask($input, $output, $question)) {
                $this->sendCampaign($campaign);
            }
        }
    }

    /**
     * @param Campaign $campaign
     */
    private function sendCampaign(Campaign $campaign)
    {
        return $this->getContainer()->get('sms.campaign.sender')->send($campaign);
    }
}