<?php

namespace ContinuousPipe\Builder\GitHub;

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
     * @param string $url
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
     * @param string $archiveFile
     * @return string
     */
    private function repackageWithTarAndWithDirectoriesAtTheRoot(Context $context, $archiveFile)
    {
        $zip = new \ZipArchive();
        if (true !== ($result = $zip->open($archiveFile))) {
            throw new \RuntimeException('Unable to unzip archive from GitHub');
        }

        $temporaryDirectory = $this->getTemporaryFilePath('tmpdir');
        mkdir($temporaryDirectory);

        $zip->extractTo($temporaryDirectory);
        $subDirectory = $context->getRepositorySubDirectory();
        if (!empty($subDirectory)) {
            $temporaryDirectory .= DIRECTORY_SEPARATOR.$subDirectory;
        }

        $finder = new Finder();
        $finder->directories()->depth(0);
        $finder->in($temporaryDirectory);

        $temporaryTarFile = $this->getTemporaryFilePath('tar').'.tar';
        $phar = new \PharData($temporaryTarFile);

        foreach ($finder as $directory) {
            $phar->buildFromDirectory($directory);
        }

        return $temporaryTarFile;
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
