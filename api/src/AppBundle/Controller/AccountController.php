<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\CodeRepository\CodeRepositoryExplorer;
use ContinuousPipe\Security\Account\Account;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route("/account/{accountUuid}", service="app.controller.account")
 * @ParamConverter("account", converter="account", options={"uuid"="accountUuid"})
 * @Security("is_granted('READ', account)")
 */
class AccountController
{
    /**
     * @var CodeRepositoryExplorer
     */
    private $codeRepositoryExplorer;

    /**
     * @param CodeRepositoryExplorer $codeRepositoryExplorer
     */
    public function __construct(CodeRepositoryExplorer $codeRepositoryExplorer)
    {
        $this->codeRepositoryExplorer = $codeRepositoryExplorer;
    }

    /**
     * @Route("/organisations", methods={"GET"})
     * @View
     */
    public function listOrganisations(Account $account)
    {
        return $this->codeRepositoryExplorer->findOrganisations($account);
    }

    /**
     * @Route("/repositories", methods={"GET"})
     * @View
     */
    public function listUserRepositories(Account $account)
    {
        return $this->codeRepositoryExplorer->findUserRepositories($account);
    }

    /**
     * @Route("/organisations/{organisationIdentifier}/repositories", methods={"GET"})
     * @View
     */
    public function listOrganisationRepositories(Account $account, string $organisationIdentifier)
    {
        return $this->codeRepositoryExplorer->findOrganisationRepositories($account, $organisationIdentifier);
    }
}
