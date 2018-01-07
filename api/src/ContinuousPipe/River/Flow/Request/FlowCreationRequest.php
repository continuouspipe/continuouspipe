<?php

namespace ContinuousPipe\River\Flow\Request;

use ContinuousPipe\River\AbstractCodeRepository;
use ContinuousPipe\River\CodeRepository;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class FlowCreationRequest
{
    /**
     * @JMS\Type("ContinuousPipe\River\AbstractCodeRepository")
     *
     * @Assert\Type("ContinuousPipe\River\AbstractCodeRepository")
     * @Assert\NotNull
     *
     * @var AbstractCodeRepository
     */
    private $repository;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $uuid;

    /**
     * @JMS\Type("string")
     *
     * @deprecated Should be removed, as using only by `FlowController:deprecatedFromRepositoryAction`
     *
     * @var string
     */
    private $team;

    /**
     * @return CodeRepository
     */
    public function getRepository() : CodeRepository
    {
        return $this->repository;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @deprecated
     *
     * @return string
     */
    public function getTeam()
    {
        return $this->team;
    }
}
