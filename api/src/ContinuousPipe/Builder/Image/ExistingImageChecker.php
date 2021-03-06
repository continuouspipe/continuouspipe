<?php

namespace ContinuousPipe\Builder\Image;

use ContinuousPipe\Builder\Aggregate\Build;
use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Request\BuildRequest;
use LogStream\Log;
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

    /**
     * @param Build $build
     *
     * @throws SearchingForExistingImageException
     *
     * @return bool
     */
    public function checkIfImagesExist(Build $build): bool
    {
        $buildRequest = $build->getRequest();
        $steps = $buildRequest->getSteps();

        $imageSteps = $this->stepsWithImages($steps);

        if (count($imageSteps) === 0) {
            return false;
        }

        if (!$this->allImagesExistAndShouldReuse($imageSteps, $buildRequest)) {
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

    private function allImagesExistAndShouldReuse(array $imageSteps, BuildRequest $buildRequest): bool
    {
        return array_reduce(
            $imageSteps,
            function (bool $allExist, BuildStepConfiguration $step) use ($buildRequest) {
                if (null === ($image = $step->getImage())) {
                    return $allExist;
                }

                return $allExist && $image->shouldReuse() !== false && $this->registry->containsImage(
                    $buildRequest->getCredentialsBucket(),
                    $image
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
            if (null === ($logIdentifier = $step->getLogStreamIdentifier())) {
                continue;
            }

            $this->loggerFactory->fromId($step->getLogStreamIdentifier())
                ->child(new Text(sprintf(
                    'Re-using already built Docker image <code>%s:%s</code>',
                    $step->getImage()->getName(),
                    $step->getImage()->getTag()
                )))
                ->updateStatus(Log::SUCCESS)
            ;
        }
    }
}
