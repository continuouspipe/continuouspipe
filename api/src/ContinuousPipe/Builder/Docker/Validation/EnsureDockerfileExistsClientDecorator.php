<?php

namespace ContinuousPipe\Builder\Docker\Validation;

use ContinuousPipe\Builder\Archive;
use ContinuousPipe\Builder\Docker\BuildContext;
use ContinuousPipe\Builder\Docker\DockerException;
use ContinuousPipe\Builder\Docker\DockerFacade;
use ContinuousPipe\Builder\Docker\PushContext;
use ContinuousPipe\Builder\Image;

class EnsureDockerfileExistsClientDecorator implements DockerFacade
{
    /**
     * @var DockerFacade
     */
    private $decoratedFacade;

    public function __construct(DockerFacade $decoratedFacade)
    {
        $this->decoratedFacade = $decoratedFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function build(BuildContext $context, Archive $archive): Image
    {
        $dockerFilePath =
            ($context->getContext()->getRepositorySubDirectory() ?: '.').
            DIRECTORY_SEPARATOR.
            ($context->getContext()->getDockerFilePath() ?: 'Dockerfile')
        ;

        if (!$archive->contains($dockerFilePath)) {
            throw new DockerException(sprintf(
                'The build configuration file `%s` was not found',
                $dockerFilePath
            ));
        }

        return $this->decoratedFacade->build($context, $archive);
    }

    /**
     * {@inheritdoc}
     */
    public function push(PushContext $context, Image $image)
    {
        return $this->decoratedFacade->push($context, $image);
    }
}
