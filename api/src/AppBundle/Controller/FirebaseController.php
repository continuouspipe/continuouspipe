<?php

namespace AppBundle\Controller;

use ContinuousPipe\Firebase\CustomAuthorizationToken;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use Firebase\ServiceAccount;
use Firebase\V3\Firebase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @Route(service="app.controller.firebase")
 */
class FirebaseController
{
    /**
     * @var string
     */
    private $serviceAccountPath;

    /**
     * @var string
     */
    private $databaseUri;

    /**
     * @param string $serviceAccountPath
     * @param string $databaseUri
     */
    public function __construct(string $serviceAccountPath, string $databaseUri)
    {
        $this->serviceAccountPath = $serviceAccountPath;
        $this->databaseUri = $databaseUri;
    }

    /**
     * @Route("/flows/{uuid}/firebase-credentials", methods={"GET"})
     * @ParamConverter("flow", converter="flow", options={"identifier"="uuid", "flat"=true})
     * @Security("is_granted('READ', flow)")
     * @View
     */
    public function credentialsAction(FlatFlow $flatFlow)
    {
        $flowUuid = (string) $flatFlow->getUuid();

        $token = CustomAuthorizationToken::create(
            ServiceAccount::fromValue($this->serviceAccountPath),
            'flow-'.$flowUuid,
            [
                'access_flow_'.$flowUuid => true,
            ],
            1800
        );

        return [
            'token' => $token->getToken(),
            'expiration_date' => $token->getExpirationDate()->format(\DateTime::ISO8601),
        ];
    }
}
