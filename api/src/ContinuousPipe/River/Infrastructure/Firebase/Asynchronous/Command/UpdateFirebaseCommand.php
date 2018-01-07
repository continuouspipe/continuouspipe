<?php

namespace ContinuousPipe\River\Infrastructure\Firebase\Asynchronous\Command;

use ContinuousPipe\Message\Message;
use JMS\Serializer\Annotation as JMS;

class UpdateFirebaseCommand implements Message
{
    const COMMAND_SET = 'set';
    const COMMAND_UPDATE = 'update';
    const COMMAND_REMOVE = 'remove';

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $command;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $databaseUri;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $path;

    /**
     * @JMS\Type("array")
     *
     * @var array|null
     */
    private $value;

    public function __construct(string $command, string $databaseUri, string $path, array $value = null)
    {
        $this->command = $command;
        $this->databaseUri = $databaseUri;
        $this->path = $path;
        $this->value = $value;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getDatabaseUri() : string
    {
        return $this->databaseUri;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * @return array|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
