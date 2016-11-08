<?php

namespace ContinuousPipe\Builder\GitHub;

use ContinuousPipe\Builder\Archive\ArchiveCreationException;
use ContinuousPipe\Builder\Archive\FileSystemArchive;
use ContinuousPipe\Builder\Context;
use ContinuousPipe\Builder\Repository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class ArchivePacker
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @param Client     $client
     * @param Repository $repository
     */
    public function __construct(Client $client, Repository $repository)
    {
        $this->client = $client;
        $this->repository = $repository;
    }

    /**
     * Create an archive from the given archive.
     *
     * @param Context $context
     * @param string  $url
     *
     * @throws ArchiveCreationException
     *
     * @return FileSystemArchive
     */
    public function createFromUrl(Context $context, $url)
    {
        $archiveFile = $this->getTemporaryFilePath('gharchive');

        try {
            $this->client->get($url, [
                'save_to' => $archiveFile,
                'headers' => [
                    'Authorization' => 'token '.$this->repository->getToken(),
                ],
            ]);
        } catch (RequestException $e) {
            throw new ArchiveCreationException('Unable to download the code archive: '.$e->getMessage(), $e->getCode(), $e);
        }

        // Extract the archive in a directory
        $archiveDirectory = $this->getArchiveDirectory($context, $archiveFile);

        // Delete the downloaded archive
        if (!unlink($archiveFile)) {
            throw new ArchiveCreationException('Unable to remove downloaded archive');
        }

        return new FileSystemArchive($archiveDirectory);
    }

    /**
     * Repackage the archive that come from GitHub in a tar, with files at root directory instead of
     * using a sub-directory.
     *
     * This methods returns the local temporary path of the created tar archive.
     *
     * @param Context $context
     * @param string  $archiveFilePath
     *
     * @return string
     *
     * @throws ArchiveCreationException
     */
    private function getArchiveDirectory(Context $context, $archiveFilePath)
    {
        $extractedArchivePath = $this->extractGitHubArchive($archiveFilePath);

        // Get build directory
        $rootProjectDirectory = $this->getRootProjectDirectory($extractedArchivePath);
        $rootBuildDirectory = $this->getRootBuildDirectory($context, $rootProjectDirectory);

        // Move the build directory into a new root directory
        $extractedNewPath = $this->getTemporaryFilePath();
        if (!rename($rootBuildDirectory, $extractedNewPath)) {
            throw new ArchiveCreationException('Unable to create temporary directory');
        }

        // Remove previous downloaded directory
        (new FileSystemArchive($extractedArchivePath))->delete();

        return $extractedNewPath;
    }

    /**
     * @param string $archiveFilePath
     *
     * @return string
     *
     * @throws ArchiveCreationException
     */
    private function extractGitHubArchive($archiveFilePath)
    {
        $temporaryDirectory = $this->getTemporaryFilePath('extractedGHArchive');
        mkdir($temporaryDirectory);

        $process = new Process(sprintf('/usr/bin/env tar -xzf %s', $archiveFilePath), $temporaryDirectory);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ArchiveCreationException('Unable to untar the archive');
        }

        $process->stop();

        return $temporaryDirectory;
    }

    /**
     * @param Context $context
     * @param string  $rootProjectDirectory
     *
     * @return string
     *
     * @throws ArchiveCreationException
     */
    private function getRootBuildDirectory(Context $context, $rootProjectDirectory)
    {
        $buildDirectory = $rootProjectDirectory;

        $subDirectory = $context->getRepositorySubDirectory();
        if (!empty($subDirectory)) {
            $buildDirectory .= DIRECTORY_SEPARATOR.$subDirectory;
        }

        if (false === ($realPath = realpath($buildDirectory))) {
            throw new ArchiveCreationException(sprintf(
                'Unable to locate the build directory at "%s"',
                $subDirectory
            ));
        }

        return $realPath;
    }

    /**
     * @param string $extractedArchivePath
     *
     * @return string
     *
     * @throws ArchiveCreationException
     */
    private function getRootProjectDirectory($extractedArchivePath)
    {
        $finder = new Finder();
        $finder->directories()->depth(0);
        $finder->in($extractedArchivePath);

        $directories = [];
        foreach ($finder as $directory) {
            $directories[] = $directory;
        }

        if (1 !== count($directories)) {
            throw new ArchiveCreationException(sprintf(
                'Expected 1 directory at the root of GitHub archive, found %d: %s',
                count($directories),
                implode(', ', $directories)
            ));
        }

        return current($directories);
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    private function getTemporaryFilePath($prefix = 'gha')
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid($prefix);
    }
}
