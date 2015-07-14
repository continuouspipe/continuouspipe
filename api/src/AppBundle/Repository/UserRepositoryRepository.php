<?php

namespace AppBundle\Repository;

use GitHub\WebHook\Model\Repository;
use Kubernetes\Manager\User\User;

interface UserRepositoryRepository
{
    /**
     * @return Repository[]
     */
    public function findByCurrentUser();

    /**
     * @param integer $id
     *
     * @return Repository
     */
    public function findById($id);
}
