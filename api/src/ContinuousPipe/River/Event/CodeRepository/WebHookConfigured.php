<?php

namespace ContinuousPipe\River\Event\CodeRepository;

use ContinuousPipe\River\CodeRepository;

class WebHookConfigured
{
    /**
     * @var CodeRepository
     */
    private $codeRepository;

    /**
     * @param CodeRepository $codeRepository
     */
    public function __construct(CodeRepository $codeRepository)
    {
        $this->codeRepository = $codeRepository;
    }
}
