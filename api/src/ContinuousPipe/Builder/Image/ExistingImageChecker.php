<?php

namespace ContinuousPipe\Builder\Image;

use ContinuousPipe\Builder\Aggregate\Build;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\LoggerFactory;
use LogStream\Node\Text;

class ExistingImageChecker
{
    private $loggerFactory;
    private $registry;

    public function __construct(LoggerFactory $loggerFactory, Registry $registry)
    {
        $this->loggerFactory = $loggerFactory;
        $this->registry = $registry;
    }

    public function checkIfImagesExist(Build $build): bool
    {
        $buildRequest = $build->getRequest();
        $steps = $buildRequest->getSteps();

        $imageSteps = $this->stepsWithImages($steps);

        if (count($imageSteps) === 0) {
            return false;
        }

        if (!$this->allImagesExist($imageSteps, $buildRequest)) {
            return false;
        }

        $this->logReusingImages($steps);

        return true;
    }

    /**
     * @param BuildStepConfiguration[] $steps
     * @return BuildStepConfiguration[]
     */
    private function stepsWithImages($steps): array
    {
        return array_filter(
            $steps,
            function (BuildStepConfiguration $step) {
                return $step->getImage() !== null;
            }
        );
    }

    private function allImagesExist(array $imageSteps, BuildRequest $buildRequest): bool
    {
        return array_reduce(
            $imageSteps,
            function (bool $allExist, BuildStepConfiguration $step) use ($buildRequest) {
                return $allExist && $this->registry->containsImage(
                    $buildRequest->getCredentialsBucket(),
                    $step->getImage()
                );
            },
            true
        );
    }

    /**
     * @param BuildStepConfiguration[] $steps
     */
    private function logReusingImages(array $steps)
    {
        foreach ($steps as $step) {
            $this->loggerFactory->fromId($step)->child(
                new Text(
                    sprintf(
                        'Re-using pre-built Docker image %s:%s',
                        $step->getImage()->getName(),
                        $step->getImage()->getTag()
                    )
                )
            );
        }
    }
}