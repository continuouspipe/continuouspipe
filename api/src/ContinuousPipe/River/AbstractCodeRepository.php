<?php

namespace ContinuousPipe\River;

/**
 * The purpose of this abstract class is only to be able to use the map configuration
 * of JMS serializer and Doctrine.
 */
abstract class AbstractCodeRepository implements CodeRepository
{
    /**
     * @var string
     */
    protected $identifier;
}
