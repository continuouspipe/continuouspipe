<?php

namespace ContinuousPipe\River\Flow;

final class Configuration
{
    /**
     * @var array
     */
    private $configuration;

    /**
     * @var bool
     */
    private $continuousPipeFileExists;

    /**
     * @param array $configuration
     * @param bool $continuousPipeFileExists
     */
    public function __construct(array $configuration, bool $continuousPipeFileExists)
    {
        $this->configuration = $configuration;
        $this->continuousPipeFileExists = $continuousPipeFileExists;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * @return bool
     */
    public function isContinuousPipeFileExists(): bool
    {
        return $this->continuousPipeFileExists;
    }
}
