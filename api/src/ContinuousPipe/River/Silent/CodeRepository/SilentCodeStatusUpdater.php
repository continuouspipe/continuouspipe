<?php

namespace ContinuousPipe\River\Silent\CodeRepository;

use ContinuousPipe\River\Tide;
use ContinuousPipe\River\Tide\Status\Status;

class SilentCodeStatusUpdater implements Tide\Status\CodeStatusUpdater
{
    /**
     * @var \ContinuousPipe\River\Tide\Status\CodeStatusUpdater
     */
    private $decoratedUpdater;

    /**
     * @param \ContinuousPipe\River\Tide\Status\CodeStatusUpdater $decoratedUpdater
     */
    public function __construct(Tide\Status\CodeStatusUpdater $decoratedUpdater)
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
     * {@inheritdoc}
     */
    public function update(Tide $tide, Status $status)
    {
        if ($this->isSilent($tide)) {
            return;
        }

        $this->decoratedUpdater->update($tide, $status);
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
