<?php

namespace ContinuousPipe\River\Flow\ConfigurationEnhancer;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\Flow\ConfigurationEnhancer;
use ContinuousPipe\River\Flow\Projections\FlatFlow;

class AddGitHubNotificationsByDefault implements ConfigurationEnhancer
{
    /**
     * {@inheritdoc}
     */
    public function enhance(FlatFlow $flow, CodeReference $codeReference, array $configs)
    {
        $defaultConfiguration = [
            'notifications' => [
                'default' => [
                    'commit' => true,
                    'pull_request' => true,
                ],
            ],
        ];

        array_unshift($configs, $defaultConfiguration);

        return $configs;
    }
}
