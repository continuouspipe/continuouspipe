<?php

namespace ContinuousPipe\Security\Team;

use ContinuousPipe\Security\Credentials\BucketContainer;
use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\Uuid;

class Team implements BucketContainer
{
    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Uuid
     */
    private $bucketUuid;

    /**
     * @var TeamMembership[]|ArrayCollection
     */
    private $memberships;

    /**
     * @param string           $slug
     * @param string           $name
     * @param Uuid             $bucketUuid
     * @param TeamMembership[] $memberships
     */
    public function __construct($slug, $name, Uuid $bucketUuid = null, array $memberships = [])
    {
        $this->slug = $slug;
        $this->name = $name;
        $this->bucketUuid = $bucketUuid;
        $this->memberships = new ArrayCollection($memberships);
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return TeamMembership[]|ArrayCollection
     */
    public function getMemberships()
    {
        if (null === $this->memberships) {
            $this->memberships = new ArrayCollection();
        }

        return $this->memberships;
    }

    /**
     * @return Uuid
     */
    public function getBucketUuid()
    {
        if (is_string($this->bucketUuid)) {
            $this->bucketUuid = Uuid::fromString($this->bucketUuid);
        }

        return $this->bucketUuid;
    }

    /**
     * @param Uuid $uuid
     */
    public function setBucketUuid(Uuid $uuid)
    {
        $this->bucketUuid = $uuid;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
