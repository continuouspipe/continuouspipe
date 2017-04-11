<?php

namespace ContinuousPipe\Builder\Archive;

use ContinuousPipe\Builder\Archive;
use Docker\Context\Context;
use Symfony\Component\Filesystem\Filesystem;

class FileSystemArchive extends Context implements Archive
{
    private $fileSystemStream;
    private $fileSystemProcess;

    public static function fromStream($resource)
    {
        $archive = new self(self::createDirectory('fs-from-stream'));
        $archive->writeStream('/', $resource);

        return $archive;
    }

    public static function copyFrom(string $path) : Archive
    {
        $directory = self::createDirectory('fs-copied');

        foreach (
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST) as $item
        ) {
            if ($item->isDir()) {
                mkdir($directory . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy($item, $directory . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
        }

        return new self($directory);
    }

    /**
     * Create an empty temporary directory with the given prefix.
     *
     * @param string $prefix
     *
     * @return string
     */
    public static function createDirectory(string $prefix)
    {
        $directory = tempnam(sys_get_temp_dir(), $prefix);
        if (file_exists($directory)) {
            unlink($directory);
        }

        mkdir($directory);

        return $directory;
    }

    /**
     * Read the archive, with a given format.
     *
     * @param string $format
     *
     * @return resource
     */
    public function read(string $format = self::FORMAT_TAR)
    {
        if (null === $format) {
            return parent::read();
        }

        if ($format == self::TAR) {
            $options = 'c';
        } else if ($format == self::TAG_GZ) {
            $options = 'cz';
        } else {
            throw new \InvalidArgumentException(sprintf('The format "%s" is not supported'));
        }

        if (!is_resource($this->fileSystemProcess)) {
            $this->fileSystemProcess = proc_open("/usr/bin/env tar ".$options." .", [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes, $this->getDirectory());
            $this->fileSystemStream = $pipes[1];
        }

        return $this->fileSystemStream;
    }

    /**
     * Delete the archive.
     */
    public function delete()
    {
        $fileSystem = new Filesystem();
        $fileSystem->remove($this->getDirectory());
    }

    /**
     * {@inheritdoc}
     */
    public function contains(string $path): bool
    {
        return file_exists($this->getDirectory().DIRECTORY_SEPARATOR.$path);
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $path, Archive $archive)
    {
        $this->writeStream($path, $archive->read());
    }

    /**
     * @param string $path
     * @param resource $stream
     *
     * @throws ArchiveException
     */
    private function writeStream(string $path, $stream)
    {
        $pipesDescription = [
            ['pipe', 'r'], // stdin
            ['pipe', 'w'], // stdout
            ['pipe', 'w'], // stderr
        ];

        $targetPath = $this->getDirectory().$path;
        if (!file_exists($targetPath)) {
            mkdir($targetPath, 0777, true);
        }

        $targetRealPath = realpath($targetPath);
        if (strpos($targetRealPath, $this->getDirectory()) !== 0) {
            throw new ArchiveException(sprintf(
                'The path "%s" is not valid or not authorized',
                $path
            ));
        }

        $this->fileSystemProcess = proc_open('/usr/bin/env tar x --strip-components=1', $pipesDescription, $pipes, $targetPath);
        if (!is_resource($this->fileSystemProcess)) {
            throw new ArchiveException('Unable to open a stream to write the artifact');
        }

        try {
            while (!feof($stream)) {
                if (false === fwrite($pipes[0], fread($stream, 4096))) {
                    throw new ArchiveException('Unable to copy the artifact stream into the archive');
                }
            }

            if (false === fclose($stream) || false == fclose($pipes[0])) {
                throw new ArchiveException('Unable to close the artifact to archive stream');
            }

            $error = stream_get_contents($pipes[2]);

            if (!empty($error)) {
                throw new ArchiveException('Something went wrong while un-taring the stream: '.$error);
            }
        } finally {
            @fclose($stream);
            @fclose($pipes[0]);
            @fclose($pipes[1]);
            @fclose($pipes[2]);

            proc_close($this->fileSystemProcess);
        }
    }

    public function __destruct()
    {
        parent::__destruct();

        if (is_resource($this->fileSystemProcess)) {
            proc_close($this->fileSystemProcess);
        }

        if (is_resource($this->fileSystemStream)) {
            fclose($this->fileSystemStream);
        }
    }
}
