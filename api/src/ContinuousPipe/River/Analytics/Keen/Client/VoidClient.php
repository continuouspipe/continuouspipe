<?php

namespace ContinuousPipe\River\Analytics\Keen\Client;

class VoidClient implements KeenClient
{
    /**
     * {@inheritdoc}
     */
    public function addEvent($collection, array $event)
    {
    }
}
