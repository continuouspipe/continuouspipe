<?php

namespace ContinuousPipe\DockerCompose\Parser;

use ContinuousPipe\DockerCompose\FileNotFound;
use ContinuousPipe\DockerCompose\RelativeFileSystem;

class FileParser
{
    /**
     * @var YamlParser
     */
    private $parser;

    /**
     * @param YamlParser $parser
     */
    public function __construct(YamlParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param RelativeFileSystem $fileSystem
     * @param string             $filePath
     *
     * @throws FileNotFound
     *
     * @return array
     */
    public function parse(RelativeFileSystem $fileSystem, $filePath)
    {
        $contents = $fileSystem->getContents($filePath);

        return $this->parser->parse($contents);
    }
}
