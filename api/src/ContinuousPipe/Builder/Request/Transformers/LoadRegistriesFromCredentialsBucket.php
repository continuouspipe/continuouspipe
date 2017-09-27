<?php

namespace ContinuousPipe\Builder\Request\Transformers;

use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Request\BuildRequestException;
use ContinuousPipe\Builder\Request\BuildRequestTransformer;
use ContinuousPipe\Security\Credentials\BucketNotFound;
use ContinuousPipe\Security\Credentials\BucketRepository;

class LoadRegistriesFromCredentialsBucket implements BuildRequestTransformer
{
    /**
     * @var BucketRepository
     */
    private $bucketRepository;

    /**
     * @param BucketRepository $bucketRepository
     */
    public function __construct(BucketRepository $bucketRepository)
    {
        $this->bucketRepository = $bucketRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(BuildRequest $request): BuildRequest
    {
        return $request->withSteps(array_map(function (BuildStepConfiguration $step) use ($request) {
            if (!empty($step->getDockerRegistries())) {
                return $step;
            }

            try {
                return $step->withDockerRegistries(
                    $this->bucketRepository->find($request->getCredentialsBucket())->getDockerRegistries()->getValues()
                );
            } catch (BucketNotFound $e) {
                throw new BuildRequestException('The credentials bucket proposed was not found', $e->getCode(), $e);
            }
        }, $request->getSteps()));
    }
}
