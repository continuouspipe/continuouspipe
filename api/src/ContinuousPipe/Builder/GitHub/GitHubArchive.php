<?php

namespace ContinuousPipe\Builder\GitHub;

use ContinuousPipe\Builder\Archive;
use GuzzleHttp\Client;

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
}
