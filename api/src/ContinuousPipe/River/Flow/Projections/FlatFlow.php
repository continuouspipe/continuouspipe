<?php

namespace ContinuousPipe\River\Flow\Projections;

use ContinuousPipe\Builder\Repository;
use ContinuousPipe\River\CodeRepository;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\View\Tide;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @var CodeRepository\RepositoryIdentifier
     */
    private $repositoryIdentifier;

    /**
     * @var Team
     */
    private $team;

    /**
     * @var User
     */
    private $user;

    /**
     * @var array
     */
    private $configuration;

    /**
     * @var ArrayCollection|FlatPipeline[]
     */
    private $pipelines;

    /**
     * @var Tide[]
     */
    private $tides;

    /**
     * @var CodeRepository|null
     */
    private $repository;

    public function __construct()
    {
        $this->pipelines = new ArrayCollection();
    }

    /**
     * @param Flow $flow
     *
     * @return FlatFlow
     */
    public static function fromFlow(Flow $flow)
    {
        $view = new self();
        $view->uuid = $flow->getUuid();
        $view->team = $flow->getTeam();
        $view->configuration = $flow->getConfiguration() ?: [];
        $view->ymlConfiguration = Yaml::dump($view->configuration);
        $view->user = $flow->getUser();
        $view->pipelines = new ArrayCollection($flow->getPipelines());
        $view->repository = $flow->getCodeRepository();

        return $view;
    }

    /**
     * @param FlatFlow $flow
     * @param Tide[]   $tides
     *
     * @return FlatFlow
     */
    public static function fromFlowAndTides(FlatFlow $flow, array $tides)
    {
        $view = clone $flow;
        $view->tides = $tides;

        return $view;
    }

    public function getUuid() : UuidInterface
    {
        return $this->uuid;
    }

    public function getRepository()
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

    /**
     * @return FlatPipeline[]|Collection
     */
    public function getPipelines()
    {
        return $this->pipelines;
    }
}
