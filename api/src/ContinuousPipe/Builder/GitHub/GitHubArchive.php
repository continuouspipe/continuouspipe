<?php

namespace ContinuousPipe\Builder\GitHub;

use ContinuousPipe\Builder\Archive;
use GuzzleHttp\Client;
use Symfony\Component\Finder\Finder;

class GitHubArchive implements Archive
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $address;

    /**
     * @param Client $client
     * @param string $address
     */
    public function __construct(Client $client, $address)
    {
        $this->address = $address;
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function isStreamed()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $archiveFile = $this->getTemporaryFilePath('gharchive');

        // Download the file to the temporary archive file
        $this->client->get($this->address, [
            'save_to' => $archiveFile,
        ]);

        $repackagedArchivePath = $this->repackageWithTarAndWithDirectoriesAtTheRoot($archiveFile);

        return fopen($repackagedArchivePath, 'r');
    }

    /**
     * Repackage the archive that come from GitHub in a tar, with files at root directory instead of
     * using a sub-directory.
     *
     * This methods returns the local temporary path of the created tar archive.
     *
     * @param string $archiveFile
     *
     * @return string
     */
    private function repackageWithTarAndWithDirectoriesAtTheRoot($archiveFile)
    {
        $zip = new \ZipArchive();
        if (true !== ($result = $zip->open($archiveFile))) {
            throw new \RuntimeException('Unable to unzip archive from GitHub');
        }

        $temporaryDirectory = $this->getTemporaryFilePath('tmpdir');
        mkdir($temporaryDirectory);

        $zip->extractTo($temporaryDirectory);
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
