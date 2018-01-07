<?php

namespace ContinuousPipe\River\Analytics\Keen\Normalizer;

use ContinuousPipe\Builder\BuildStepConfiguration;

class BuildRequestNormalizer
{
    public function normalize(BuildStepConfiguration $buildRequestStep)
    {
        $request = [
            'environment' => $buildRequestStep->getEnvironment(),
            'context' => [
                'docker_file_path' => $buildRequestStep->getContext()->getDockerFilePath(),
                'sub_directory' => $buildRequestStep->getContext()->getRepositorySubDirectory(),
            ],
        ];

        if (null !== ($image = $buildRequestStep->getImage())) {
            $request['image'] = [
                'name' => $buildRequestStep->getImage()->getName(),
                'tag' => $buildRequestStep->getImage()->getTag(),
            ];
        }

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
