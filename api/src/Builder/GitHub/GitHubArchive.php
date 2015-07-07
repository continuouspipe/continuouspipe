<?php

namespace Builder\GitHub;

use Builder\Archive;
use Symfony\Component\Finder\Finder;

class GitHubArchive implements Archive
{
    /**
     * @var string
     */
    private $address;

    /**
     * @param string $address
     */
    public function __construct($address)
    {
        $this->address = $address;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        $archiveFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('gharchive');
        file_put_contents($archiveFile, file_get_contents($this->address));

        $zip = new \ZipArchive();
        if (true !== ($result = $zip->open($archiveFile))) {
            var_dump($result, 'fuck');
            exit;
        }

        $temporaryDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('tmpdir');
        mkdir($temporaryDirectory);

        $zip->extractTo($temporaryDirectory);
        $finder = new Finder();
        $finder->directories()->depth(0);
        $finder->in($temporaryDirectory);

        $temporaryTarFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('tar').'.tar';
        $phar = new \PharData($temporaryTarFile);

        foreach ($finder as $directory) {
            $phar->buildFromDirectory($directory);
        }

        return file_get_contents($temporaryTarFile);
    }

    /**
     * {@inheritdoc}
     */
    public function isStreamed()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        return $this->getContents();
    }
}
