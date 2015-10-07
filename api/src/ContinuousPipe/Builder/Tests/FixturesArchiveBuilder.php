<?php

namespace ContinuousPipe\Builder\Tests;

use ContinuousPipe\Builder\Archive\ArchiveCreationException;
use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Tests\Archive\FileSystemArchive;
use ContinuousPipe\User\User;
use LogStream\Logger;

class FixturesArchiveBuilder implements ArchiveBuilder
{
    const ADDRESS_PREFIX = 'fixtures://';

    /**
     * @var string
     */
    private $fixturesRootPath;

    /**
     * @param string $fixturesRootPath
     */
    public function __construct($fixturesRootPath)
    {
        $this->fixturesRootPath = $fixturesRootPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getArchive(BuildRequest $buildRequest, User $user, Logger $logger)
    {
        $repositoryAddress = $buildRequest->getRepository()->getAddress();
        if (strpos($repositoryAddress, self::ADDRESS_PREFIX) === false) {
            throw new ArchiveCreationException(sprintf(
                'The repository address "%s" is not supported to get an archive',
                $repositoryAddress
            ));
        }

        $fixturesDirectory = substr($repositoryAddress, strlen(self::ADDRESS_PREFIX));
        $fixturesDirectoryPath = realpath($this->fixturesRootPath.DIRECTORY_SEPARATOR.$fixturesDirectory);

        if (!file_exists($fixturesDirectoryPath) || !is_dir($fixturesDirectoryPath)) {
            throw new ArchiveCreationException(sprintf(
                'The directory "%s" do not exists',
                $fixturesDirectoryPath
            ));
        }

        return new FileSystemArchive($fixturesDirectoryPath);
    }
}
