<?php

namespace ContinuousPipe\River\Silent\CodeRepository;

use ContinuousPipe\River\CodeRepository\CodeStatusUpdater;
use ContinuousPipe\River\Tide;

class SilentCodeStatusUpdater implements CodeStatusUpdater
{
    /**
     * @var CodeStatusUpdater
     */
    private $decoratedUpdater;

    /**
     * @param CodeStatusUpdater $decoratedUpdater
     */
    public function __construct(CodeStatusUpdater $decoratedUpdater)
    {
        $this->decoratedUpdater = $decoratedUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function success(Tide $tide)
    {
        if ($this->isSilent($tide)) {
            return;
        }

        $this->decoratedUpdater->success($tide);
    }

    /**
     * {@inheritdoc}
     */
    public function pending(Tide $tide)
    {
        if ($this->isSilent($tide)) {
            return;
        }

        $this->decoratedUpdater->pending($tide);
    }

    /**
     * {@inheritdoc}
     */
    public function failure(Tide $tide)
    {
        if ($this->isSilent($tide)) {
            return;
        }

        $this->decoratedUpdater->failure($tide);
    }

    /**
     * Returns true if the tide is configured to be silent.
     *
     * @param Tide $tide
     *
     * @return bool
     */
    private function isSilent(Tide $tide)
    {
        $configuration = $tide->getContext()->getConfiguration();

        return array_key_exists('silent', $configuration) && $configuration['silent'];
    }
}
