<?php

namespace ContinuousPipe\Adapter\Kubernetes\Listener\AfterCreatingComponent;

use ContinuousPipe\Adapter\Kubernetes\Component\ComponentAttacher;
use ContinuousPipe\Adapter\Kubernetes\Event\AfterCreatingComponent;
use ContinuousPipe\Model\Component;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AttachComponent implements EventSubscriberInterface
{
    /**
     * @var ComponentAttacher
     */
    private $componentAttacher;

    /**
     * @param ComponentAttacher $componentAttacher
     */
    public function __construct(ComponentAttacher $componentAttacher)
    {
        $this->componentAttacher = $componentAttacher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AfterCreatingComponent::NAME => 'listen',
        ];
    }

    /**
     * @param AfterCreatingComponent $event
     */
    public function listen(AfterCreatingComponent $event)
    {
        if ($this->haveToAttach($event->getComponent())) {
            $this->componentAttacher->attach($event->getContext(), $event->getStatus());
        }
    }

    /**
     * Returns true if the component have to be attached.
     *
     * @param Component $component
     *
     * @return bool
     */
    private function haveToAttach(Component $component)
    {
        if ($deploymentStrategy = $component->getDeploymentStrategy()) {
            return $deploymentStrategy->isAttached();
        }

        return false;
    }
}
