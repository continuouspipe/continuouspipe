<?php

namespace ContinuousPipe\River\CodeRepository\BitBucket\Builder;

use ContinuousPipe\Builder\BuilderException;
use ContinuousPipe\Builder\Repository;
use ContinuousPipe\Builder\Request\Archive;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\BitBucket\BitBucketCodeRepository;
use ContinuousPipe\River\CodeRepository\ImplementationDelegation\BuildRequestSourceResolverAdapter;

class BitBucketBuildRequestSourceResolver implements BuildRequestSourceResolverAdapter
{
    /**
     * {@inheritdoc}
     */
    public function getSource(CodeReference $codeReference)
    {
        $repository = $codeReference->getRepository();
        if (!$repository instanceof BitBucketCodeRepository) {
            throw new BuilderException('This build request source resolver only supports BitBucket repositories');
        }

        return new Archive(
            sprintf(
                'https://bitbucket.org/%s/%s/get/%s.tar.gz',
                $repository->getOwner()->getUsername(),
                $repository->getName(),
                $codeReference->getCommitSha() ?: $codeReference->getBranch()
            )
        );
    }

    public function supports(CodeReference $codeReference): bool
    {
        return $codeReference->getRepository() instanceof BitBucketCodeRepository;
    }
}
