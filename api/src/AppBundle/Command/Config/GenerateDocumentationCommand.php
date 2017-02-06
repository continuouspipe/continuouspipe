<?php

namespace AppBundle\Command\Config;

use AppBundle\Model\Definition\Dumper\MarkdownReferenceDumper;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This CLI command generates documentation for continuous-pipe.yml.
 */
class GenerateDocumentationCommand extends Command
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct($name = null, ConfigurationInterface $configuration)
    {
        parent::__construct($name);

        $this->configuration = $configuration;
    }

    protected function configure()
    {
        $this->setDescription('Generates documentation for continuous-pipe.yml in Markdown format.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dumper = new MarkdownReferenceDumper();

        $output->writeln($dumper->dump($this->configuration));
    }
}
