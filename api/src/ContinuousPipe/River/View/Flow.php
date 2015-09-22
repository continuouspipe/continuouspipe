<?php

namespace ContinuousPipe\River\View;

use ContinuousPipe\River\CodeRepository;
use Rhumsaa\Uuid\Uuid;
use Symfony\Component\Yaml\Yaml;

class Flow
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var CodeRepository
     */
    private $repository;

    /**
     * @var string
     */
    private $ymlConfiguration;

    /**
     * @param \ContinuousPipe\River\Flow $flow
     *
     * @return Flow
     */
    public static function fromFlow(\ContinuousPipe\River\Flow $flow)
    {
        $flowContext = $flow->getContext();

        $view = new self();
        $view->uuid = $flowContext->getFlowUuid();
        $view->repository = $flowContext->getCodeRepository();
        $view->ymlConfiguration = Yaml::dump($flowContext->getConfiguration());

        return $view;
    }

    /**
     * @return Uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }
}
