<?php

namespace ContinuousPipe\River\Flow\Projections;

use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
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
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $ymlConfiguration;

    /**
     * @var array
     */
    private $configuration;

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
        $view = new self();
        $view->uuid = $flow->getUuid();
        $view->repository = $flow->getCodeRepository();
        $view->team = $flow->getTeam();
        $view->configuration = $flow->getConfiguration() ?: [];
        $view->ymlConfiguration = Yaml::dump($view->configuration);
        $view->user = $flow->getUser();

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

    public function getUuid() : UuidInterface
    {
        return $this->uuid;
    }

    public function getRepository() : CodeRepository
    {
        return $this->repository;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function getConfiguration() : array
    {
        return $this->configuration;
    }

    public function getUser() : User
    {
        return $this->user;
    }
}
