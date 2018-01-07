<?php

namespace ContinuousPipe\River\Tide\Concurrency\Lock;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class PreventDeepLocking implements Locker
{
    /**
     * @var Locker
     */
    private $decoratedLocker;

    /**
     * @var Collection
     */
    private $lockedCollection;

    /**
     * @param Locker $decoratedLocker
     */
    public function __construct(Locker $decoratedLocker)
    {
        $this->decoratedLocker = $decoratedLocker;
        $this->lockedCollection = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function lock($name, callable $callable)
    {
        if ($this->lockedCollection->contains($name)) {
            return $callable();
        }

        $this->lockedCollection->add($name);
        return $this->decoratedLocker->lock($name, function () use ($name, $callable) {
            try {
                return $callable();
            } finally {
                $this->lockedCollection->removeElement($name);
            }
        });
    }
}
