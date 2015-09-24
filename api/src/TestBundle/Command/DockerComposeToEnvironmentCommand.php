<?php

namespace TestBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DockerComposeToEnvironmentCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this
            ->setName('docker-compose:to:environment')
            ->addArgument('filePath', InputArgument::REQUIRED)
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Name of the environment', 'default')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('filePath');
        if (!file_exists($filePath)) {
            throw new \RuntimeException(sprintf(
                'File at path "%s" do not exists',
                $filePath
            ));
        }

        $yamlLoader = $this->getContainer()->get('pipe.docker_compose.yaml_loader');
        $environment = $yamlLoader->load($input->getOption('name'), file_get_contents($filePath));

        $serializer = $this->getContainer()->get('jms_serializer');
        $serialized = $serializer->serialize($environment, 'json');

        $output->writeln($serialized);
    }
}
