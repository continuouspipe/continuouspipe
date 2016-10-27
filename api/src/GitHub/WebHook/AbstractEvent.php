<?php

namespace GitHub\WebHook;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\Discriminator(field="type", map={
 *     "ping": "GitHub\WebHook\Event\PingEvent",
 *     "pull_request": "GitHub\WebHook\Event\PullRequestEvent",
 *     "status": "GitHub\WebHook\Event\StatusEvent",
 *     "push": "GitHub\WebHook\Event\PushEvent"
 * })
 */
abstract class AbstractEvent implements Event
{
}
