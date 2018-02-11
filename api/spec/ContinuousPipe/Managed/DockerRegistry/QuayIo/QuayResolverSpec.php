<?php

namespace spec\ContinuousPipe\Managed\DockerRegistry\QuayIo;

use ContinuousPipe\Managed\DockerRegistry\DockerRegistryManagerResolver;
use ContinuousPipe\Managed\DockerRegistry\QuayIo\QuayManager;
use ContinuousPipe\Managed\DockerRegistry\QuayIo\QuayManagerResolver;
use ContinuousPipe\QuayIo\HttpQuayClient;
use ContinuousPipe\Security\Credentials\BucketRepository;
use GuzzleHttp\ClientInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class QuayResolverSpec extends ObjectBehavior
{
    function let(BucketRepository $bucketRepository, ClientInterface $httpClient)
    {
        $this->beConstructedWith($bucketRepository, $httpClient);
    }

    function it_is_a_registry_resolver()
    {
        $this->shouldImplement(DockerRegistryManagerResolver::class);
    }

    function it_supports_quay_dsn()
    {
        $this->supports('quay://something')->shouldBe(true);
        $this->supports('docker://something')->shouldBe(false);
    }

    function it_creates_a_manager_from_a_well_formed_dsn(BucketRepository $bucketRepository, ClientInterface $httpClient)
    {
        $this->get('quay://token:the-token@organisation')->shouldBeLike(new QuayManager(
            new HttpQuayClient(
                $httpClient->getWrappedObject(),
                'organisation',
                'the-token'
            ),
            $bucketRepository->getWrappedObject()
        ));
    }
}
