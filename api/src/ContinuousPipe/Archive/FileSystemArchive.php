<?php

namespace ContinuousPipe\Archive;

use ContinuousPipe\River\CodeRepository\FileSystem\LocalRelativeFileSystem;
use ContinuousPipe\River\CodeRepository\FileSystem\RelativeFileSystem;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

class FileSystemArchive implements Archive
{
    private $fileSystemStream;
    private $fileSystemProcess;

    private $directory;

    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    public static function fromStream($stream, $format = self::TAR)
    {
        $archive = new self(self::createDirectory('fs-from-stream'));
        $archive->writeStream('/', $stream, $format);

        return $archive;
    }

    public static function createEmpty() : FilesystemArchive
    {
        return new self(self::createDirectory('empty'));
    }

    public static function copyFrom(string $path) : FileSystemArchive
    {
        $directory = self::createDirectory('fs-copied');

        foreach (
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            ) as $item
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
     * {@inheritdoc}
     */
    public function read(string $format) : StreamInterface
    {
        if ($format == self::TAR) {
            $options = 'c';
        } elseif ($format == self::TAR_GZ) {
            $options = 'cz';
        } else {
            throw new \InvalidArgumentException(sprintf('The format "%s" is not supported'));
        }

        $this->fileSystemProcess = proc_open("/usr/bin/env tar ".$options." .", [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes, $this->directory);

        if (false === ($contents = stream_get_contents($pipes[1]))) {
            throw new ArchiveException('Cannot re-package the archive');
        }

        $error = stream_get_contents($pipes[2]);
        if (!empty($error)) {
            throw new ArchiveException('Something went wrong while reading the stream: '.$error);
        }

        if (0 !== ($status = proc_close($this->fileSystemProcess))) {
            throw new ArchiveException('Turning source code into an archive went wrong, got status %d', $status);
        }

        @fclose($pipes[0]);
        @fclose($pipes[1]);
        @fclose($pipes[2]);

        return \GuzzleHttp\Psr7\stream_for($contents);
    }

    /**
     * @throws ArchiveException
     */
    public function delete()
    {
        $fileSystem = new Filesystem();

        try {
            $fileSystem->remove($this->directory);
        } catch (IOException $e) {
            throw new ArchiveException('Cannot delete the archive', $e->getCode(), $e);
        }
    }

    public function contains(string $path): bool
    {
        return file_exists($this->directory.DIRECTORY_SEPARATOR.$path);
    }

    public function writeFile(string $path, string $contents)
    {
        if (false === file_put_contents($this->directory.DIRECTORY_SEPARATOR.$path, $contents)) {
            throw new ArchiveException(sprintf('Unable to write file "%s"', $path));
        }
    }

    /**
     * @param string $path
     * @param resource|StreamInterface $stream
     * @param string $format
     *
     * @throws ArchiveException
     */
    private function writeStream(string $path, $stream, $format)
    {
        $stream = \GuzzleHttp\Psr7\stream_for($stream);
        $pipesDescription = [
            ['pipe', 'r'], // stdin
            ['pipe', 'w'], // stdout
            ['pipe', 'w'], // stderr
        ];

        $targetPath = $this->directory.DIRECTORY_SEPARATOR.$path;
        if (!file_exists($targetPath)) {
            mkdir($targetPath, 0777, true);
        }

        $targetRealPath = realpath($targetPath);
        if (strpos($targetRealPath, $this->directory) !== 0) {
            throw new ArchiveException(sprintf(
                'The path "%s" is not valid or not authorized',
                $path
            ));
        }

        if ($format == self::TAR) {
            $options = 'x';
        } elseif ($format == self::TAR_GZ) {
            $options = 'xz';
        } else {
            throw new \InvalidArgumentException(sprintf('The format "%s" is not supported', $format));
        }

        $this->fileSystemProcess = proc_open('/usr/bin/env tar '.$options.' --strip-components=1', $pipesDescription, $pipes, $targetPath);
        if (!is_resource($this->fileSystemProcess)) {
            throw new ArchiveException('Unable to open a stream to write the artifact');
        }

        try {
            while (!$stream->eof()) {
                if (false === ($wrote = fwrite($pipes[0], $stream->read(4096)))) {
                    throw new ArchiveException('Unable to copy the artifact stream into the archive');
                }
            }

            if (false === $stream->close() || false === fclose($pipes[0])) {
                throw new ArchiveException('Unable to close the artifact to archive stream');
            }

            $error = stream_get_contents($pipes[2]);
            if (!empty($error)) {
                throw new ArchiveException('Something went wrong while un-taring the stream: '.$error);
            }
        } finally {
            $stream->close();

            @fclose($pipes[0]);
            @fclose($pipes[1]);
            @fclose($pipes[2]);

            proc_close($this->fileSystemProcess);
        }
    }

    public function __destruct()
    {
        if (is_resource($this->fileSystemProcess)) {
            proc_close($this->fileSystemProcess);
        }

        if (is_resource($this->fileSystemStream)) {
            fclose($this->fileSystemStream);
        }
    }

    public function getFilesystem() : RelativeFileSystem
    {
        return new LocalRelativeFileSystem($this->directory);
    }

    /**
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }
}
