<?php

namespace AppBundle\Controller;

use ContinuousPipe\Authenticator\Security\ApiKey\UserApiKey;
use ContinuousPipe\Authenticator\Security\ApiKey\UserByApiKeyRepository;
use ContinuousPipe\Security\User\User;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route(path="/account/api-keys", service="app.controller.api_keys")
 */
class ApiKeysController
{
    /**
     * @var UserByApiKeyRepository
     */
    private $userByApiKeyRepository;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        UserByApiKeyRepository $userByApiKeyRepository,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->userByApiKeyRepository = $userByApiKeyRepository;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @Route("", name="account_api_keys")
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @Template
     */
    public function listAction(User $user)
    {
        return [
            'user' => $user,
            'keys' => $this->userByApiKeyRepository->findByUser($user->getUsername())
        ];
    }

    /**
     * @Route("/create", name="account_api_keys_create", methods={"POST"})
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @Template
     */
    public function createAction(User $user, Request $request)
    {
        if (empty($description = $request->request->get('description'))) {
            $request->getSession()->getFlashBag()->add('danger', 'The description have to be filled-in');
        } else {
            $this->userByApiKeyRepository->save(new UserApiKey(
                Uuid::uuid4(),
                $user,
                Uuid::uuid4()->toString(),
                new \DateTime(),
                $description
            ));

            $request->getSession()->getFlashBag()->add('success', 'API key successfully created');
        }

        return new RedirectResponse($this->urlGenerator->generate(
            'account_api_keys'
        ));
    }

    /**
     * @Route("/{uuid}/delete", name="account_api_keys_delete", methods={"POST"})
     * @ParamConverter("user", converter="user", options={"fromSecurityContext"=true})
     * @Template
     */
    public function deleteAction(Request $request, User $user, string $uuid)
    {
        try {
            $this->userByApiKeyRepository->delete(
                $user->getUsername(),
                Uuid::fromString($uuid)
            );

            $request->getSession()->getFlashBag()->add('success', 'API key successfully deleted');
        } catch (\Exception $e) {
            $request->getSession()->getFlashBag()->add('danger', $e->getMessage());
        }


        return new RedirectResponse($this->urlGenerator->generate(
            'account_api_keys'
        ));
    }
}
