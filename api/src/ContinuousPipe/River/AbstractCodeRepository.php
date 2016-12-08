<?php

namespace ContinuousPipe\River;

use JMS\Serializer\Annotation as JMS;

/**
 * The purpose of this abstract class is only to be able to use the map configuration
 * of JMS serializer and Doctrine.
 */
abstract class AbstractCodeRepository implements CodeRepository
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $identifier;
}
