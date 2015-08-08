<?php

namespace ContinuousPipe\Builder\Tests;

use ContinuousPipe\Builder\Archive;

class FakeArchive implements Archive
{
    /**
     * @var string
     */
    private $contents;

    /**
     * @param string $contents
     */
    public function __construct($contents)
    {
        $this->contents = $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function isStreamed()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        return $this->contents;
    }
}
