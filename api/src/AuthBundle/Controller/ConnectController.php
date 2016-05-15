<?php

namespace AuthBundle\Controller;

use HWI\Bundle\OAuthBundle\Controller\ConnectController as HWIOAuthBundleConnectController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ConnectController extends HWIOAuthBundleConnectController
{
    public function connectAction(Request $request)
    {
        if ($this->isUserAuthenticated()) {
            return new RedirectResponse($this->generate('logged_in_page'));
        }

        if ($error = $this->getErrorForRequest($request)) {
            $error = $error->getMessage();
        }

        return $this->container->get('templating')->renderResponse('HWIOAuthBundle:Connect:login.html.' . $this->getTemplatingEngine(), array(
            'error'   => $error,
        ));
    }

    /**
     * @return bool
     */
    private function isUserAuthenticated()
    {
        return $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED');
    }
}
