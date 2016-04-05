<?php

namespace ContinuousPipe\River\Tide\StartVoter;

use ContinuousPipe\River\Event\GitHub\PullRequestSynchronized;
use ContinuousPipe\River\Tide;
use GitHub\WebHook\Event\PullRequestEvent;

class ContextChangedVoter implements TideStartVoter
{
    /**
     * {@inheritdoc}
     */
    public function vote(Tide $tide, Tide\Configuration\ArrayObject $context)
    {
        return $this->tideStartedBecauseOfALabel($tide) && $this->tideDependsOnLabels($tide);
    }

    /**
     * @param Tide $tide
     *
     * @return bool
     */
    private function tideStartedBecauseOfALabel(Tide $tide)
    {
        $event = $tide->getContext()->getCodeRepositoryEvent();
        if (!$event instanceof PullRequestSynchronized) {
            return false;
        }

        return $event->getEvent()->getAction() == PullRequestEvent::ACTION_LABELED;
    }

    /**
     * @param Tide $tide
     *
     * @return bool
     */
    private function tideDependsOnLabels(Tide $tide)
    {
        $configuration = $tide->getContext()->getConfiguration();
        $filters = $this->extractFilters($configuration);

        foreach ($filters as $filter) {
            if (strpos($filter, 'labels') !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $configuration
     *
     * @return array
     */
    private function extractFilters(array $configuration)
    {
        $filters = [];

        if (array_key_exists('filter', $configuration)) {
            $filters[] = $configuration['filter'];
        }

        foreach ($configuration['tasks'] as $task) {
            if (array_key_exists('filter', $task)) {
                $filters[] = $task['filter']['expression'];
            }
        }

        return $filters;
    }
}
