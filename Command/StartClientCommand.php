<?php

namespace Terox\SmsCampaignBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Terox\SmsCampaignBundle\TeroxSmsCampaignBundle;

/**
 * Signal New.
 *
 */
class StartClientCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sms:start:client')
            ->setDescription('Start SMPP client')
            ->addOption(
                'provider',
                'p',
                InputOption::VALUE_REQUIRED,
                'Provider that generates the signal'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Parameters
        $providerCodename = $input->getOption('provider');

        // Get provider
        $provider = $this->getContainer()->get('sms.repository.provider')->findOneBy([
            'code'    => $providerCodename,
            'enabled' => true
        ]);

        if(null === $provider) {
            return $output->writeln(
                sprintf('<fg=red>The provider with coden: "%s" is no enabled or does not exists</>', $providerCodename)
            );
        }

        $config = $this->getContainer()->getParameter(TeroxSmsCampaignBundle::BUNDLE_NAMESPACE.'.config');

        if(!isset($config['providers'][$provider->getCode()])) {
            return $output->writeln(sprintf('<fg=red>The configuration "%s" is not available</>', $provider->getConfigName()));
        }

        $currentConfig = $config['providers'][$provider->getCode()];
        $builder = new ProcessBuilder();
        $builder
            ->setPrefix($currentConfig['rpc']['exec'])
            ->add('listen')
            ->add($currentConfig['rpc']['port'])
            ->add(sprintf('--host=%s', $currentConfig['host']))
            ->add(sprintf('--port=%s', $currentConfig['port']))
            ->add(sprintf('--login=%s', $currentConfig['login']))
            ->add(sprintf('--password=%s', $currentConfig['password']));

        if(!empty($currentConfig['rpc']['dlr'])) {
            $builder->add(sprintf('--dlr-callback=%s', $currentConfig['rpc']['callback']));
        }

        $process = new Process($builder->getProcess()->getCommandLine(). ' &');

        $output->writeln(sprintf('<fg=green>Executing: %s...</>', $process->getCommandLine()));

        $process->run();
        $output->write(sprintf('<fg=green>%s</>', $process->getOutput()));
        $output->write(sprintf('<fg=red>%s</>', $process->getErrorOutput()));
    }
}