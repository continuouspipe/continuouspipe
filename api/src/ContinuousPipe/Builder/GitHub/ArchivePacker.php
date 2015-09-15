<?php

namespace ContinuousPipe\Builder\GitHub;

use ContinuousPipe\Builder\Archive\ArchiveCreationException;
use ContinuousPipe\Builder\Archive\FileSystemArchive;
use ContinuousPipe\Builder\Context;
use GuzzleHttp\Client;
use Symfony\Component\Finder\Finder;

class ArchivePacker
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
     * Create an archive from the given ZIP archive.
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

        // Download the file to the temporary archive file
        $this->client->get($url, [
            'save_to' => $archiveFile,
        ]);

        $filePath = $this->repackageWithTarAndWithDirectoriesAtTheRoot($context, $archiveFile);

        return new FileSystemArchive($filePath);
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
     */
    private function repackageWithTarAndWithDirectoriesAtTheRoot(Context $context, $archiveFilePath)
    {
        $extractedArchivePath = $this->extractGitHubArchive($archiveFilePath);

        // Get build directory
        $rootProjectDirectory = $this->getRootProjectDirectory($extractedArchivePath);
        $rootBuildDirectory = $this->getRootBuildDirectory($context, $rootProjectDirectory);

        // Create the TAR archive
        $temporaryTarFile = $this->getTemporaryFilePath('tar').'.tar';
        $phar = new \PharData($temporaryTarFile);
        $phar->buildFromDirectory($rootBuildDirectory);

        return $temporaryTarFile;
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
        $zip = new \ZipArchive();
        if (true !== ($result = $zip->open($archiveFilePath))) {
            throw new ArchiveCreationException('Unable to unzip archive from GitHub');
        }

        $temporaryDirectory = $this->getTemporaryFilePath('extractedGHArchive');
        mkdir($temporaryDirectory);

        $zip->extractTo($temporaryDirectory);

        return $temporaryDirectory;
    }

    /**
     * @param Context $context
     * @param string  $rootProjectDirectory
     *
     * @return string
     */
    private function getRootBuildDirectory(Context $context, $rootProjectDirectory)
    {
        $buildDirectory = $rootProjectDirectory;

        $subDirectory = $context->getRepositorySubDirectory();
        if (!empty($subDirectory)) {
            $buildDirectory .= DIRECTORY_SEPARATOR.$subDirectory;
        }

        return $buildDirectory;
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
