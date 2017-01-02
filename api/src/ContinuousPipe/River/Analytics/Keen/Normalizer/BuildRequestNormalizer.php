<?php

namespace ContinuousPipe\River\Analytics\Keen\Normalizer;

use ContinuousPipe\Builder\Request\BuildRequest;

class BuildRequestNormalizer
{
    public function normalize(BuildRequest $buildRequest)
    {
        $request = [
            'environment' => $buildRequest->getEnvironment(),
            'image' => [
                'name' => $buildRequest->getImage()->getName(),
                'tag' => $buildRequest->getImage()->getTag(),
            ],
            'context' => [
                'docker_file_path' => $buildRequest->getContext()->getDockerFilePath(),
                'sub_directory' => $buildRequest->getContext()->getRepositorySubDirectory(),
            ],
        ];

        if (null !== ($repository = $buildRequest->getRepository())) {
            $request['repository'] = [
                'address' => $repository->getAddress(),
                'branch' => $repository->getBranch(),
            ];
        }

        if (null !== ($archive = $buildRequest->getArchive())) {
            $request['archive'] = [
                'url' => parse_url($archive->getUrl()),
            ];
        }

        return $request;
    }
}
