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
        $dockerFilePath = $context->getContext()->getDockerFilePath() ?: 'Dockerfile';

        if (!$archive->contains($dockerFilePath)) {
            $message = 'The build configuration file `%s` was not found';

            if ($explorationFolder = $context->getContext()->getRepositorySubDirectory()) {
                $message .= ' (in `%s`)';
            }

            throw new DockerException(sprintf(
                $message,
                $dockerFilePath,
                $explorationFolder
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
