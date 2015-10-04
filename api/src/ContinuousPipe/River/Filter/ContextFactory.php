<?php

namespace ContinuousPipe\River\Filter;

use ContinuousPipe\River\Filter\View\CodeReferenceView;
use ContinuousPipe\River\Tide;

class ContextFactory
{
    /**
     * Create the context available in tasks' filters.
     *
     * @param Tide $tide
     *
     * @return array
     */
    public function create(Tide $tide)
    {
        $context = $tide->getContext();

        return [
            'codeReference' => CodeReferenceView::fromCodeReference($context->getCodeReference()),
        ];
    }
}
