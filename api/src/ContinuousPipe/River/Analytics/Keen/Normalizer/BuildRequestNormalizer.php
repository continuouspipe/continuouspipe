<?php

namespace ContinuousPipe\River\Analytics\Keen\Normalizer;

use ContinuousPipe\Builder\Request\BuildRequestStep;

class BuildRequestNormalizer
{
    public function normalize(BuildRequestStep $buildRequestStep)
    {
        $request = [
            'environment' => $buildRequestStep->getEnvironment(),
            'image' => [
                'name' => $buildRequestStep->getImage()->getName(),
                'tag' => $buildRequestStep->getImage()->getTag(),
            ],
            'context' => [
                'docker_file_path' => $buildRequestStep->getContext()->getDockerFilePath(),
                'sub_directory' => $buildRequestStep->getContext()->getRepositorySubDirectory(),
            ],
        ];

        if (null !== ($repository = $buildRequestStep->getRepository())) {
            $request['repository'] = [
                'address' => $repository->getAddress(),
                'branch' => $repository->getBranch(),
            ];
        }

        if (null !== ($archive = $buildRequestStep->getArchive())) {
            $request['archive'] = [
                'url' => parse_url($archive->getUrl()),
            ];
        }

        return $request;
    }
}
