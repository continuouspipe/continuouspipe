<?php

namespace ContinuousPipe\Builder\Archive;

use ContinuousPipe\Builder\Context;
use ContinuousPipe\Builder\Request\ArchiveSource;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

class HttpArchivePacker implements ArchivePacker
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create an archive from the given archive.
     *
     * @param Context $context
     * @param ArchiveSource $archive
     *
     * @return FileSystemArchive
     *
     * @throws ArchiveCreationException
     */
    public function createFromArchiveRequest(Context $context, ArchiveSource $archive)
    {
        $archiveFile = $this->getTemporaryFilePath('archive');

        try {
            $this->client->get($archive->getUrl(), [
                'save_to' => $archiveFile,
                'headers' => $archive->getHeaders(),
            ]);
        } catch (RequestException $e) {
            if (null !== ($response = $e->getResponse())) {
                try {
                    $contents = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

                    if (isset($contents['error']['message'])) {
                        $message = $contents['error']['message'];
                    }
                    if (isset($contents['error']['code'])) {
                        $code = $contents['error']['code'];
                    }
                } catch (\InvalidArgumentException $errorException) {
                    // Handle the exception as if it wasn't supported
                }
            }

            if (!isset($message)) {
                $message = $e->getMessage();
            }
            if (!isset($code)) {
                $code = $e->getCode();
            }

            throw new ArchiveCreationException('Unable to download the code archive: '.$message, $code, $e);
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
                'Expected 1 directory at the root of the archive, found %d: %s',
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
