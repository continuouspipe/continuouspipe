<?php

namespace WorkerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HealthCheckCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('worker:health-check')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->getDocker()->getMiscManager()->getVersion();

            $output->writeln('<info>- Getting Docker informations</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>Unable to get Docker informations: '.$e->getMessage());
        }
    }

    /**
     * @return \Docker\Docker
     */
    private function getDocker()
    {
        return $this->getContainer()->get('docker');
    }
}
