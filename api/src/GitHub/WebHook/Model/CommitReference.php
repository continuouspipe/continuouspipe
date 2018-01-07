<?php

namespace GitHub\WebHook\Model;

use JMS\Serializer\Annotation as JMS;

class CommitReference
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $label;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("ref")
     */
    private $reference;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("sha")
     */
    private $sha1;

    /**
     * @var Repository
     *
     * @JMS\Type("GitHub\WebHook\Model\Repository")
     * @JMS\SerializedName("repo")
     */
    private $repository;

    /**
     * @var Repository
     *
     * @JMS\Type("GitHub\WebHook\Model\User")
     */
    private $user;

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getSha1()
    {
        return $this->sha1;
    }

    /**
     * @return Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return Repository
     */
    public function getUser()
    {
        return $this->user;
    }
}
