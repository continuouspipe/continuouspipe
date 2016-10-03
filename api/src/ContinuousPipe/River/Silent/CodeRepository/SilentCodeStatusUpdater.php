<?php

namespace ContinuousPipe\River\Silent\CodeRepository;

use ContinuousPipe\River\Tide\Status\CodeStatusUpdater;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\River\Tide\Status\Status;

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
        $configuration = $tide->getConfiguration();

        return array_key_exists('silent', $configuration) && $configuration['silent'];
    }
}
