<?php

namespace ContinuousPipe\River\Task\Deploy\DockerCompose\Transformer;

use ContinuousPipe\River\CodeRepository\DockerCompose\DockerComposeComponent;
use ContinuousPipe\River\Task\Deploy\DeployContext;
use ContinuousPipe\River\Task\Deploy\DockerCompose\DockerComposeTransformer;

class ReplaceBuildByFinalImagesTransformer implements DockerComposeTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform(DeployContext $context, array $parsed)
    {
        foreach ($parsed as $componentName => &$rawComponent) {
            $dockerComposeComponent = DockerComposeComponent::fromParsed($rawComponent);
            if (!$dockerComposeComponent->hasToBeBuilt()) {
                continue;
            }

            $rawComponent['image'] = $dockerComposeComponent->getImageName();
            unset($rawComponent['build']);
        }

        return $parsed;
    }
}
