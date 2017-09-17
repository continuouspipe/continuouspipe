<?php

namespace AppBundle\Controller;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeArchiveStreamer;
use ContinuousPipe\River\CodeRepository\CodeRepositoryException;
use ContinuousPipe\River\CodeRepository\GitHub\Builder\GitHubBuildRequestSourceResolver;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\Flow\Projections\FlatFlow;
use GitHub\Integration\InstallationNotFound;
use GitHub\Integration\InstallationRepository;
use GitHub\Integration\InstallationTokenException;
use GitHub\Integration\InstallationTokenResolver;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route(service="app.controller.code_archive")
 */
class CodeArchiveController
{
    /**
     * @var CodeArchiveStreamer
     */
    private $codeArchiveStreamer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CodeArchiveStreamer $codeArchiveStreamer,
        LoggerInterface $logger
    ) {
        $this->codeArchiveStreamer = $codeArchiveStreamer;
        $this->logger = $logger;
    }

    /**
     * @Route("/flows/{flowUuid}/source-code/archive/{reference}", methods={"GET"}, name="flow_source_code_archive")
     * @ParamConverter("flow", converter="flow", options={"identifier"="flowUuid", "flat"=true})
     * @View
     */
    public function flowArchiveProxyAction(FlatFlow $flow, string $reference)
    {
        try {
            $archiveStream = $this->codeArchiveStreamer->streamCodeArchive($flow->getUuid(), new CodeReference(
                $flow->getRepository(),
                $reference
            ));
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException('The repository for this flow is not supported');
        } catch (CodeRepositoryException $e) {
            $this->logger->warning('Unable to download the source code from the code repository', [
                'exception' => $e,
                'flow_uuid' => $flow->getUuid()->toString(),
            ]);

            return new JsonResponse([
                'error' => $e->getMessage(),
            ], 500);
        }

        return new StreamedResponse(function () use ($archiveStream) {
            while (!$archiveStream->eof()) {
                echo $archiveStream->read(1024);
            }
        }, 200, [
            'Content-Type' => 'application/gzip',
        ]);
    }

    /**
     * @Route("/github/flows/{flowUuid}/source-code/archive/{reference}", methods={"GET"}, name="deprecated_flow_source_code_archive")
     * @ParamConverter("flow", converter="flow", options={"identifier"="flowUuid", "flat"=true})
     * @View
     */
    public function deprecatedFlowArchiveProxyAction(FlatFlow $flow, string $reference)
    {
        return $this->flowArchiveProxyAction($flow, $reference);
    }
}
