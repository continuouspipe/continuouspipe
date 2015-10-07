<?php

namespace ContinuousPipe\Builder\Archive\Reader;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Archive\ArchiveReader;

class DownloaderArchiveReader implements ArchiveReader
{
    /**
     * {@inheritdoc}
     */
    public function getFileContents(Archive $archive, $path)
    {
        $extractDirectoryPath = $this->getExtractedDirectory($archive, $path);
        $extractedFilePath = $extractDirectoryPath.DIRECTORY_SEPARATOR.$path;
        if (!file_exists($extractedFilePath)) {
            throw new Archive\ArchiveException(sprintf(
                'Expected to find extracted file at "%s" but file is not found',
                $extractedFilePath
            ));
        }

        return file_get_contents($extractedFilePath);
    }

    /**
     * {@inheritdoc}
     */
    public function extract(Archive $archive, $path = null)
    {
        if (null == $path) {
            $path = $this->getTemporaryDirectoryPath();
        }

        $archivePath = $this->getLocalArchiveFile($archive);
        try {
            $phar = new \PharData($archivePath);
            $phar->extractTo($path);
        } catch (\PharException $e) {
            throw new Archive\ArchiveException($e->getMessage(), $e->getCode(), $e);
        }

        return $path;
    }

    /**
     * @param Archive $archive
     * @param string  $path
     *
     * @return string
     *
     * @throws Archive\ArchiveException
     */
    private function getExtractedDirectory(Archive $archive, $path)
    {
        if ($archive instanceof Archive\FileSystemArchive) {
            return $archive->getDirectory();
        }

        $archivePath = $this->getLocalArchiveFile($archive);
        $extractDirectoryPath = $this->getTemporaryDirectoryPath();
        $path = $this->removePathPrefixes($path);

        try {
            $phar = new \PharData($archivePath);
            $phar->extractTo($extractDirectoryPath, $path);
        } catch (\PharException $e) {
            throw new Archive\ArchiveException($e->getMessage(), $e->getCode(), $e);
        }

        return $extractDirectoryPath;
    }

    /**
     * @param string $prefix
     *
     * @return string
     *
     * @throws Archive\ArchiveException
     */
    private function getTemporaryDirectoryPath($prefix = 'archive-downloader')
    {
        $path = $this->getTemporaryPath($prefix);
        if (!mkdir($path)) {
            throw new Archive\ArchiveException(sprintf(
                'Unable to create temporary directory with path "%s"',
                $path
            ));
        }

        return $path;
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    private function getTemporaryPath($prefix = 'archive-downloader')
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid($prefix);
    }

    /**
     * @param Archive $archive
     *
     * @return string
     *
     * @throws Archive\ArchiveException
     */
    private function getLocalArchiveFile(Archive $archive)
    {
        $archivePath = $this->getTemporaryPath().'.tar';

        if (!$archive->isStreamed()) {
            file_put_contents($archivePath, $archive->read());

            return $archivePath;
        }

        $archiveStream = $archive->read();
        $archiveFilePointer = fopen($archivePath, 'w');

        while (!feof($archiveStream)) {
            fwrite($archiveFilePointer, fread($archiveStream, 8192));
        }

        fclose($archiveStream);
        fclose($archiveFilePointer);

        return $archivePath;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function removePathPrefixes($path)
    {
        if (substr($path, 0, 1) == '/') {
            $path = substr($path, 1);
        } elseif (substr($path, 0, 2) == './') {
            $path = substr($path, 2);
        }

        return $path;
    }
}
