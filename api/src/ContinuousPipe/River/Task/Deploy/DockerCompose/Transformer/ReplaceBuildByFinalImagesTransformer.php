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
            $dockerComposeComponent = DockerComposeComponent::fromParsed($componentName, $rawComponent);
            if (!$dockerComposeComponent->hasToBeBuilt()) {
                continue;
            }

            $rawComponent['image'] = $this->getComponentImage($dockerComposeComponent, $context);
            unset($rawComponent['build']);
        }

        return $parsed;
    }

    /**
     * Resolve the component image name.
     *
     * @param DockerComposeComponent $component
     * @param DeployContext          $context
     *
     * @return string
     */
    private function getComponentImage(DockerComposeComponent $component, DeployContext $context)
    {
        $imageName = $component->getImageName();

        if (!$this->imageHasTag($imageName)) {
            $imageName .= ':'.$context->getCodeReference()->getBranch();
        }

        return $imageName;
    }
}
