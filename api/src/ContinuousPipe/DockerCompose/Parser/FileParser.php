<?php

namespace ContinuousPipe\DockerCompose\Parser;

use ContinuousPipe\DockerCompose\DockerComposeException;
use ContinuousPipe\DockerCompose\FileNotFound;
use ContinuousPipe\DockerCompose\RelativeFileSystem;
use Symfony\Component\Yaml\Exception\ParseException;

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
     * @throws DockerComposeException
     *
     * @return array
     */
    public function parse(RelativeFileSystem $fileSystem, $filePath)
    {
        $contents = $fileSystem->getContents($filePath);

        try {
            return $this->parser->parse($contents);
        } catch (ParseException $e) {
            throw new DockerComposeException('Unable to parse the file "'.$filePath.'": '.$e->getMessage(), $e->getCode(), $e);
        }
    }
}
