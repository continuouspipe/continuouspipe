<?php

namespace ContinuousPipe\Builder\Tests;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Archive\ArchiveCreationException;
use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Request\ArchiveSource;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Tests\Archive\NonDeletableFileSystemArchive;
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
    public function createArchive(BuildStepConfiguration $buildStepConfiguration) : Archive
    {
        $repositoryAddress = $buildStepConfiguration->getRepository()->getAddress();
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
                $fixturesDirectory
            ));
        }

        return new Archive\FileSystemArchive($this->copy($fixturesDirectoryPath));
    }

    /**
     * {@inheritdoc}
     */
    public function supports(BuildStepConfiguration $buildStepConfiguration) : bool
    {
        if (null === ($repository = $buildStepConfiguration->getRepository())) {
            return false;
        }

        return strpos($repository->getAddress(), self::ADDRESS_PREFIX) !== false;
    }

    /**
     * Copy the contents of the given directory and return the path of another one.
     *
     * @param string $fixturesDirectoryPath
     *
     * @return string
     */
    private function copy(string $fixturesDirectoryPath) : string
    {
        $directory = tempnam(sys_get_temp_dir(), 'fs-fixtures');
        if (file_exists($directory)) {
            unlink($directory);
            mkdir($directory);
        }

        foreach (
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fixturesDirectoryPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST) as $item
        ) {
            if ($item->isDir()) {
                mkdir($directory . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy($item, $directory . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }

        return $directory;
    }
}
