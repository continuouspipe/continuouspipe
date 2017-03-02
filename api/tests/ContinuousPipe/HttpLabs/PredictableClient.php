<?php

namespace ContinuousPipe\HttpLabs;

use ContinuousPipe\HttpLabs\Client\HttpLabsClient;
use ContinuousPipe\HttpLabs\Client\HttpLabsException;
use ContinuousPipe\HttpLabs\Client\Stack;

class PredictableClient implements HttpLabsClient
{
    /**
     * @var Stack|null
     */
    private $futureStack;

    /**
     * {@inheritdoc}
     */
    public function createStack(string $apiKey, string $projectIdentifier, string $backendUrl): Stack
    {
        if (null === $this->futureStack) {
            throw new HttpLabsException('The future of the stack wasn\'t predicated. Where\'s the crystal ball?');
        }

        return $this->futureStack;
    }

    /**
     * @param Stack|null $futureStack
     */
    public function setFutureStack($futureStack)
    {
        $this->futureStack = $futureStack;
    }
}
