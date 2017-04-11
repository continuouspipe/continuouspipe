<?php

namespace ContinuousPipe\Builder\Docker;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Engine;
use ContinuousPipe\Builder\Image;

class FacadeMap implements DockerFacade
{
    /**
     * @var DockerFacade[]
     */
    private $facades;

    /**
     * FacadeMap constructor.
     * @param DockerFacade[] $facades
     */
    public function __construct(array $facades)
    {
        $this->validateFacades($facades);
        $this->facades = $facades;
    }

    /**
     * @param BuildContext $context
     * @param Archive $archive
     *
     * @throws DockerException
     *
     * @return Image
     */
    public function build(BuildContext $context, Archive $archive) : Image
    {
        return $this->facades[$context->getEngine()->getType()]->build($context, $archive);
    }

    /**
     * @param PushContext $context
     * @param Image $image
     *
     * @return DockerFacade|mixed
     * @throws DockerException
     */
    public function push(PushContext $context, Image $image)
    {
        return $this->facades[$context->getEngine()->getType()]->push($context, $image);
    }

    /**
     * @param array $facades
     */
    private function validateFacades(array $facades)
    {
        $facadeKeys = array_keys($facades);
        sort($facadeKeys);
        $expectedKeys = Engine::TYPES;
        sort($expectedKeys);

        if ($facadeKeys != $expectedKeys) {
            throw new \InvalidArgumentException('Need to provide facades for all engine types');
        }

        foreach ($facades as $facade) {
            if (!($facade instanceof DockerFacade)) {
                throw new \InvalidArgumentException('Facades should implement DockerFacade');
            }
        }
    }
}
