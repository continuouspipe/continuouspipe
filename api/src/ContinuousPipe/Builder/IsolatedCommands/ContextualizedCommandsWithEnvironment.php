<?php

namespace ContinuousPipe\Builder\IsolatedCommands;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Build;

class ContextualizedCommandsWithEnvironment implements CommandExtractor
{
    /**
     * @var CommandExtractor
     */
    private $commandExtractor;

    /**
     * @param CommandExtractor $commandExtractor
     */
    public function __construct(CommandExtractor $commandExtractor)
    {
        $this->commandExtractor = $commandExtractor;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommands(Build $build, Archive $archive)
    {
        $commands = $this->commandExtractor->getCommands($build, $archive);

        $environmentVariables = $build->getRequest()->getEnvironment();
        if (count($environmentVariables) > 0) {
            $commands = array_map(function ($command) use ($environmentVariables) {
                return $this->decoratesCommandWithEnvironmentVariables($command, $environmentVariables);
            }, $commands);
        }

        return $commands;
    }

    /**
     * {@inheritdoc}
     */
    public function getArchiveWithStrippedDockerfile(Build $build, Archive $archive)
    {
        return $this->commandExtractor->getArchiveWithStrippedDockerfile($build, $archive);
    }

    /**
     * @param string $command
     * @param array  $environmentVariables
     *
     * @return string
     */
    private function decoratesCommandWithEnvironmentVariables($command, array $environmentVariables)
    {
        $quotedCommand = str_replace('\'', '\\\'', $command);
        $environmentExports = array_map(function ($key, $value) {
            return $key.'='.$value;
        }, array_keys($environmentVariables), $environmentVariables);

        return sprintf(
            '%s sh -c \'%s\'',
            implode(' ', $environmentExports),
            $quotedCommand
        );
    }
}
