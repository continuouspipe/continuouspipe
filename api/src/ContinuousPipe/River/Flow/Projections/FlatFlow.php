<?php

namespace ContinuousPipe\River\Flow\Projections;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\Security\Team\Team;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Yaml\Yaml;

class FlatFlow
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
     * @var Team
     */
    private $team;

    /**
     * @var string
     */
    private $ymlConfiguration;

    /**
     * @var Tide[]
     */
    private $tides;

    /**
     * @param \ContinuousPipe\River\Flow $flow
     *
     * @return FlatFlow
     */
    public static function fromFlow(\ContinuousPipe\River\Flow $flow)
    {
        $flowContext = $flow->getContext();

        $view = new self();
        $view->uuid = $flowContext->getFlowUuid();
        $view->repository = $flowContext->getCodeRepository();
        $view->team = $flowContext->getTeam();
        $view->ymlConfiguration = Yaml::dump($flowContext->getConfiguration());

        return $view;
    }

    /**
     * @param \ContinuousPipe\River\Flow $flow
     * @param Tide[]                     $tides
     *
     * @return FlatFlow
     */
    public static function fromFlowAndTides(\ContinuousPipe\River\Flow $flow, array $tides)
    {
        $view = self::fromFlow($flow);
        $view->tides = $tides;

        return $view;
    }

    /**
     * @return Uuid
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return CodeRepository
     */
    public function getRepository() : CodeRepository
    {
        return $this->repository;
    }

    /**
     * @return Team
     */
    public function getTeam(): Team
    {
        return $this->team;
    }
}
