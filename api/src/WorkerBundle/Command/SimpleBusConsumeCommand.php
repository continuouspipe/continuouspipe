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
            ->addOption('with-headers', 'w', InputOption::VALUE_NONE, 'The message includes the headers')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->getMessage($input);

        // Get consumer
        $consumer = $this->getContainer()->get($input->getOption('consumer'));
        $consumed = $consumer->execute($message);

        return $consumed ? 0 : 1;
    }

    /**
     * @param InputInterface $input
     *
     * @return AMQPMessage
     */
    protected function getMessage(InputInterface $input)
    {
        $message = base64_decode($input->getArgument('message'));

        if ($input->getOption('with-headers')) {
            $json = json_decode($message, true);

            $message = new AMQPMessage($json['body'], $json['properties']);
        } else {
            $message = new AMQPMessage($message);
        }

        return $message;
    }
}
