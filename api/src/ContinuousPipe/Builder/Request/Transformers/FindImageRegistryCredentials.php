<?php

namespace ContinuousPipe\Builder\Request\Transformers;

use ContinuousPipe\Builder\BuildStepConfiguration;
use ContinuousPipe\Builder\Docker\CredentialsRepository;
use ContinuousPipe\Builder\Request\BuildRequest;
use ContinuousPipe\Builder\Request\BuildRequestException;
use ContinuousPipe\Builder\Request\BuildRequestTransformer;
use ContinuousPipe\Security\Authenticator\CredentialsNotFound;

class FindImageRegistryCredentials implements BuildRequestTransformer
{
    /**
     * @var CredentialsRepository
     */
    private $credentialsRepository;

    /**
     * @param CredentialsRepository $credentialsRepository
     */
    public function __construct(CredentialsRepository $credentialsRepository)
    {
        $this->credentialsRepository = $credentialsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function transform(BuildRequest $request): BuildRequest
    {
        return $request->withSteps(array_map(function (BuildStepConfiguration $step) use ($request) {
            if (null !== $step->getImageRegistryCredentials() || null === $step->getImage()) {
                return $step;
            }

            try {
                return $step->withImageRegistryCredentials(
                    $this->credentialsRepository->findByImage(
                        $step->getImage(),
                        $request->getCredentialsBucket()
                    )
                );
            } catch (CredentialsNotFound $e) {
                throw new BuildRequestException(sprintf(
                    'Docker Registry credentials for the image "%s" not found',
                    $step->getImage()->getName()
                ), $e->getCode(), $e);
            }
        }, $request->getSteps()));
    }
}
