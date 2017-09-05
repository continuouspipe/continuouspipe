<?php

namespace ContinuousPipe\River\Message;

use ContinuousPipe\Message\Message;

/**
 * Describes a message that is "operational". This means that it is not a mandatory piece of the system
 * and won't suffer from being a little bit late.
 *
 */
interface OperationalMessage extends Message
{
}
