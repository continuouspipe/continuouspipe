<?php

namespace ContinuousPipe\River\Tests\CodeRepository\GitHub;

use GitHub\WebHook\Model\Repository;
use GitHub\WebHook\Model\WebHook;
use GitHub\WebHook\Setup\WebHookManager;

class FakeWebHookManager implements WebHookManager
{
    /**
     * {@inheritdoc}
     */
    public function setup(Repository $repository, WebHook $webHook)
    {
        return $webHook;
    }
}
