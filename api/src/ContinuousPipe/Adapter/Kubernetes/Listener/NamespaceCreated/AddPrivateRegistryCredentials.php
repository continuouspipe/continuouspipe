<?php

namespace ContinuousPipe\Adapter\Kubernetes\Listener\NamespaceCreated;

use ContinuousPipe\Adapter\Kubernetes\Event\NamespaceCreated;

class AddPrivateRegistryCredentials
{
    public function notify(NamespaceCreated $event)
    {
        $providerName = $event->getContext()->getDeployment()->getRequest()->getProviderName();
    }
}
