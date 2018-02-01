<?php

namespace ContinuousPipe\Builder\Archive;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\ArchiveBuilder;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Context;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class DownloadAndRepackArchiveBuilder implements ArchiveBuilder
{
    private $archiveDownloader;

    public function __construct(ArchiveDownloader $archiveDownloader)
    {
        $this->archiveDownloader = $archiveDownloader;
    }

    /**
     * {@inheritdoc}
     */
    public function createArchive(BuildStepConfiguration $buildStepConfiguration) : Archive
    {
        $archiveFile = $this->getTemporaryFilePath('archive');

        try {
            $this->archiveDownloader->download($buildStepConfiguration->getArchive(), $archiveFile);
        } catch (ArchiveException $e) {
            throw new ArchiveCreationException('Unable to download the code archive: '.$e->getMessage(), $e->getCode(), $e);
        }

        // Extract the archive in a directory
        $archiveDirectory = $this->getArchiveDirectory($buildStepConfiguration->getContext(), $archiveFile);

        // Delete the downloaded archive
        if (!unlink($archiveFile)) {
            throw new ArchiveCreationException('Unable to remove downloaded archive');
        }

        return new FileSystemArchive($archiveDirectory);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(BuildStepConfiguration $buildStepConfiguration) : bool
    {
        return $buildStepConfiguration->getArchive() !== null;
    }

    /**
     * Repackage the archive that come from the tar, with files at root directory instead of
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
        $temporaryDirectory = $this->getTemporaryFilePath('extractedArchive');
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
     * @param string  $buildDirectory
     *
     * @return string
     *
     * @throws ArchiveCreationException
     */
    private function getRootBuildDirectory(Context $context, $buildDirectory)
    {
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

    private function getRootProjectDirectory($extractedArchivePath) : string
    {
        $finder = new Finder();
        $finder->depth(0);
        $finder->in($extractedArchivePath);

        $directories = [];
        $files = [];
        foreach ($finder as $directoryOrFile) {
            if (is_dir($directoryOrFile)) {
                $directories[] = $directoryOrFile;
            } else {
                $files[] = $directoryOrFile;
            }
        }

        if (1 === count($directories) && 0 === count($files)) {
            return current($directories);
        }

        return $extractedArchivePath;
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
