<?php

namespace ContinuousPipe\Builder\IsolatedCommands;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Build;
use ContinuousPipe\Builder\Docker\DockerfileResolver;

class DockerfileCommandExtractor implements CommandExtractor
{
    const BOUNDARY = '#~continuous-pipe:inject-environment~#';

    /**
     * @var Archive\ArchiveReader
     */
    private $archiveReader;

    /**
     * @var DockerfileResolver
     */
    private $dockerfileResolver;

    /**
     * @var Archive\Mutable\MutableArchiveBuilder
     */
    private $mutableArchiveBuilder;

    /**
     * @param Archive\ArchiveReader                 $archiveReader
     * @param DockerfileResolver                    $dockerfileResolver
     * @param Archive\Mutable\MutableArchiveBuilder $mutableArchiveBuilder
     */
    public function __construct(Archive\ArchiveReader $archiveReader, DockerfileResolver $dockerfileResolver, Archive\Mutable\MutableArchiveBuilder $mutableArchiveBuilder)
    {
        $this->archiveReader = $archiveReader;
        $this->dockerfileResolver = $dockerfileResolver;
        $this->mutableArchiveBuilder = $mutableArchiveBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommands(Build $build, Archive $archive)
    {
        $dockerfileContents = $this->getDockerfileContents($build, $archive);
        $commands = [];
        $foundBoundary = false;
        foreach (explode("\n", $dockerfileContents) as $line) {
            if (strpos($line, self::BOUNDARY) !== false) {
                $foundBoundary = true;

                continue;
            }

            if ($foundBoundary && ($command = $this->extractCommand($line))) {
                $commands[] = $command;
            }
        }

        return $commands;
    }

    /**
     * {@inheritdoc}
     */
    public function getArchiveWithStrippedDockerfile(Build $build, Archive $archive)
    {
        $dockerfilePath = $this->dockerfileResolver->getFilePath($build->getRequest()->getContext());
        $dockerfileContents = $this->archiveReader->getFileContents($archive, $dockerfilePath);
        $boundaryPosition = strpos($dockerfileContents, self::BOUNDARY);
        if (false === $boundaryPosition) {
            return $archive;
        }

        // Get the first part before the boundary
        $dockerfileContents = substr($dockerfileContents, 0, $boundaryPosition);

        $mutableArchive = $this->mutableArchiveBuilder->createFromArchive($archive);
        $mutableArchive->write($dockerfilePath, $dockerfileContents);

        return $mutableArchive->getArchive();
    }

    /**
     * @param string $line
     *
     * @return string|null
     */
    private function extractCommand($line)
    {
        if (preg_match('#RUN ([^\#]+)#i', $line, $matches)) {
            return $matches[1];
        }

        return;
    }

    /**
     * @param Build   $build
     * @param Archive $archive
     *
     * @return string
     */
    private function getDockerfileContents(Build $build, Archive $archive)
    {
        $path = $this->dockerfileResolver->getFilePath($build->getRequest()->getContext());
        $contents = $this->archiveReader->getFileContents($archive, $path);

        return $contents;
    }
}
