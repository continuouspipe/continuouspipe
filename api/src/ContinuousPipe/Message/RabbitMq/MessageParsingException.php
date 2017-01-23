<?php

namespace ContinuousPipe\Message\RabbitMq;

use ContinuousPipe\Worker\InvalidArgumentException;

class MessageParsingException extends \InvalidArgumentException implements InvalidArgumentException
{
}
