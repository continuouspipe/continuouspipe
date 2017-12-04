<?php

namespace River\BitBucket;

use Behat\Behat\Context\Context;
use ContinuousPipe\AtlassianAddon\Installation;
use ContinuousPipe\AtlassianAddon\TraceableInstallationRepository;
use Helpers\FixturesHelper;
use Helpers\KernelClientHelper;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class AddonContext implements Context
{
    use KernelClientHelper;
    use FixturesHelper;

    /**
     * @var TraceableInstallationRepository
     */
    private $traceableInstallationRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        KernelInterface $kernel,
        TraceableInstallationRepository $traceableInstallationRepository,
        SerializerInterface $serializer
    ) {
        $this->kernel = $kernel;
        $this->traceableInstallationRepository = $traceableInstallationRepository;
        $this->serializer = $serializer;
    }

    /**
     * @When the add-on :clientKey is installed for the user account :principalUsername
     */
    public function theAddOnIsInstalledForTheUserAccount($clientKey, $principalUsername)
    {
        $addon = $this->createAddonArray($clientKey, $principalUsername);

        $this->response = $this->kernel->handle(Request::create(
            '/connect/service/bitbucket/addon/installed',
            'POST',
            [], [], [],
            [
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($addon)
        ));

        $this->assertResponseCode(204);
    }

    /**
     * @When the add-on :clientKey is uninstalled for the user account :principalUsername
     */
    public function theAddOnIsUninstalledForTheUserAccount($clientKey, $principalUsername)
    {
        $addon = $this->createAddonArray($clientKey, $principalUsername);

        $this->response = $this->kernel->handle(Request::create(
            '/connect/service/bitbucket/addon/uninstalled',
            'POST',
            [], [], [],
            [
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($addon)
        ));

        $this->assertResponseCode(204);
    }

    /**
     * @Then the add-on :clientKey should be removed
     */
    public function theAddOnShouldBeRemoved($clientKey)
    {
        $saved = $this->traceableInstallationRepository->getRemoved();

        if (count($saved) == 0) {
            throw new \RuntimeException('Found 0 removed installation');
        }
    }

    /**
     * @Then the installation should be saved
     */
    public function theInstallationShouldBeSaved()
    {
        $saved = $this->traceableInstallationRepository->getSaved();

        if (count($saved) == 0) {
            throw new \RuntimeException('Found 0 saved installation');
        }
    }

    /**
     * @Given there is the add-on :clientKey installed for the user account :principalUsername
     */
    public function thereIsTheAddOnInstalledForTheUserAccount($clientKey, $principalUsername)
    {
        $addon = $this->createAddonArray($clientKey, $principalUsername);
        $installation = $this->serializer->deserialize(
            json_encode($addon),
            Installation::class,
            'json'
        );

        $this->traceableInstallationRepository->save($installation);
    }

    /**
     * @Given there is the add-on installed for the BitBucket repository :repositoryName owned by user :ownerUsername
     */
    public function thereIsTheAddOnInstalledForTheBitbucketRepositoryOwnedByUser($repositoryName, $ownerUsername)
    {
        $addon = $this->createAddonArray('test-client-key', $ownerUsername);
        $installation = $this->serializer->deserialize(
            json_encode($addon),
            Installation::class,
            'json'
        );

        $this->traceableInstallationRepository->save($installation);
    }

    private function createAddonArray(string $clientKey, string $principalUsername): array
    {
        $addon = \GuzzleHttp\json_decode($this->readFixture('addon-installed.json'), true);
        $addon['clientKey'] = $clientKey;
        $addon['principal']['type'] = 'user';
        $addon['principal']['username'] = $principalUsername;

        return $addon;
    }

    private function readFixture($fixture)
    {
        return $this->loadFixture($fixture, 'river/integrations/code-repositories/bitbucket');
    }
}
