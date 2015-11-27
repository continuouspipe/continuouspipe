<?php

namespace ContinuousPipe\River\Analytics\Keen\Normalizer;

use ContinuousPipe\Builder\Request\BuildRequest;

class BuildRequestNormalizer
{
    public function normalize(BuildRequest $buildRequest)
    {
        return [
            'environment' => $buildRequest->getEnvironment(),
            'image' => [
                'name' => $buildRequest->getImage()->getName(),
                'tag' => $buildRequest->getImage()->getTag(),
            ],
            'context' => [
                'docker_file_path' => $buildRequest->getContext()->getDockerFilePath(),
                'sub_directory' => $buildRequest->getContext()->getRepositorySubDirectory(),
            ],
            'repository' => [
                'address' => $buildRequest->getRepository()->getAddress(),
                'branch' => $buildRequest->getRepository()->getBranch(),
            ],
        ];
    }
}
