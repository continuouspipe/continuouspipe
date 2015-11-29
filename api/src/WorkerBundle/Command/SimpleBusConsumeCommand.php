<?php

namespace WorkerBundle\Command;

use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SimpleBusConsumeCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('simple-bus:consume')
            ->addArgument('message', InputArgument::REQUIRED)
            ->addOption('consumer', 'c', InputOption::VALUE_REQUIRED, 'Service ID of the consumer', 'simple_bus.rabbit_mq_bundle_bridge.commands_consumer')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get body from `rabbitmq-cli-consumer`
        $body = base64_decode($input->getArgument('message'));
        $message = new AMQPMessage($body);

        // Get consumer
        $consumer = $this->getContainer()->get($input->getOption('consumer'));
        $consumed = $consumer->execute($message);

        return $consumed ? 0 : 1;
    }
}
