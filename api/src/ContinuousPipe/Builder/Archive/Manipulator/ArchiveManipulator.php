<?php

namespace ContinuousPipe\Builder\Archive\Manipulator;

use ContinuousPipe\Builder\Archive;

class ArchiveManipulator
{
    /**
     * @var Archive\ArchiveReader
     */
    private $archiveReader;

    /**
     * @var Archive
     */
    private $archive;

    /**
     * This variable contains the extracted archive directory.
     *
     * If the value is null, that means it was never extracted so no modification have been made.
     *
     * @var string|null
     */
    private $extractedDirectory = null;

    /**
     * @param Archive\ArchiveReader $archiveReader
     * @param Archive               $archive
     */
    public function __construct(Archive\ArchiveReader $archiveReader, Archive $archive)
    {
        $this->archiveReader = $archiveReader;
        $this->archive = $archive;
    }

    /**
     * Write the content of the following file in the archive.
     *
     * @param string $path
     * @param string $contents
     *
     * @throws Archive\ArchiveException
     */
    public function write($path, $contents)
    {
        $filePath = $this->getExtractedArchiveDirectory().DIRECTORY_SEPARATOR.$path;
        if (false === file_put_contents($filePath, $contents)) {
            throw new Archive\ArchiveException(sprintf(
                'Unable to write content to file "%s"',
                $path
            ));
        }
    }

    /**
     * @return Archive
     */
    public function getArchive()
    {
        return $this->archiveHasBeenExtracted() ? $this->generateArchive() : $this->archive;
    }

    /**
     * @return null|string
     */
    private function getExtractedArchiveDirectory()
    {
        if (!$this->archiveHasBeenExtracted()) {
            $this->extractedDirectory = $this->archiveReader->extract($this->archive);
        }

        return $this->extractedDirectory;
    }

    /**
     * @return bool
     */
    private function archiveHasBeenExtracted()
    {
        return null !== $this->extractedDirectory;
    }

    /**
     * @return Archive\FileSystemArchive
     */
    private function generateArchive()
    {
        $tarFilePath = $this->getTemporaryFilePath('tar').'.tar';
        $phar = new \PharData($tarFilePath);
        $phar->buildFromDirectory($this->extractedDirectory);

        return new Archive\FileSystemArchive($tarFilePath);
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    private function getTemporaryFilePath($prefix = 'am')
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid($prefix);
    }
}
