<?php

namespace GitHub\WebHook\Request\ParamConverter;

use GitHub\WebHook\EventClassNotFound;
use GitHub\WebHook\GitHubRequest;
use GitHub\WebHook\RequestDeserializer;
use GitHub\WebHook\Security\InvalidRequest;
use GitHub\WebHook\Security\RequestValidator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GitHubRequestConverter implements ParamConverterInterface
{
    /**
     * @var RequestValidator
     */
    private $requestValidator;

    /**
     * @var RequestDeserializer
     */
    private $requestDeserializer;

    /**
     * @param RequestValidator    $requestValidator
     * @param RequestDeserializer $requestDeserializer
     */
    public function __construct(RequestValidator $requestValidator, RequestDeserializer $requestDeserializer)
    {
        $this->requestValidator = $requestValidator;
        $this->requestDeserializer = $requestDeserializer;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        try {
            $this->checkHeaders($request);
            $this->requestValidator->validate($request);
        } catch (InvalidRequest $e) {
            throw new AccessDeniedHttpException($e->getMessage(), $e);
        }

        try {
            $event = $this->requestDeserializer->deserialize($request);
        } catch (InvalidRequest $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        } catch (EventClassNotFound $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        $deliveryId = $request->headers->get(RequestDeserializer::HEADER_DELIVERY);
        $githubRequest = new GitHubRequest($deliveryId, $event);

        $request->attributes->set($configuration->getName(), $githubRequest);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() === 'githubRequest';
    }

    /**
     * @param Request $request
     *
     * @throws InvalidRequest
     */
    private function checkHeaders(Request $request)
    {
        $requiredHeaders = [RequestDeserializer::HEADER_EVENT, RequestDeserializer::HEADER_DELIVERY];

        foreach ($requiredHeaders as $header) {
            if (!$request->headers->has($header)) {
                throw new InvalidRequest(sprintf('Missing "%s" header', $header));
            }
        }
    }
}
