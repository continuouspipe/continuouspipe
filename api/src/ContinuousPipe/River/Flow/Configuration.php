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
    private $hasContinuousPipeFile;

    /**
     * @param array $configuration
     * @param bool $hasContinuousPipeFile
     */
    public function __construct(array $configuration, bool $hasContinuousPipeFile)
    {
        $this->configuration = $configuration;
        $this->hasContinuousPipeFile = $hasContinuousPipeFile;
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
    public function hasContinuousPipeFile(): bool
    {
        return $this->hasContinuousPipeFile;
    }
}
