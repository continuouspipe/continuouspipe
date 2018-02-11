<?php

namespace spec\ContinuousPipe\Managed\ClusterCreation\GoogleCloud;

use ContinuousPipe\Google\ContainerEngineCluster;
use ContinuousPipe\Google\ContainerEngineClusterRepository;
use ContinuousPipe\Managed\ClusterCreation\GoogleCloud\GKEClusterAccountCreator;
use ContinuousPipe\Security\Account\GoogleAccount;
use ContinuousPipe\Security\Team\Team;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GKEClusterAccountCreatorSpec extends ObjectBehavior
{
    function let(ContainerEngineClusterRepository $containerEngineClusterRepository, ClientInterface $httpClient)
    {
        $client = new \Google_Client();
        $client->setHttpClient($httpClient->getWrappedObject());

        $this->beConstructedWith($containerEngineClusterRepository, $client);
    }

    function it_supports_gke_dsn()
    {
        $this->supports(new Team('my-team', 'Team'), 'cluster', 'gke://');
    }

    function it_creates_a_managed_cluster(ClientInterface $httpClient, ContainerEngineClusterRepository $containerEngineClusterRepository)
    {
        $httpClient->send(Argument::that(function(Request $request) {
            return $request->getUri()->getPath() == '/v1/projects/project-id/serviceAccounts/team-my-team@project-id.iam.gserviceaccount.com';
        }), [])->willReturn(new Response(200, [], \GuzzleHttp\json_encode([
            'service_account' => '',
        ])));

        // Create the key
        $createdServiceAccount = base64_encode(\GuzzleHttp\json_encode([
            'type' => 'service-account',
            'from' => 'created-key',
        ]));

        $httpClient->send(Argument::that(function(Request $request) {
            return $request->getMethod() == 'POST' && $request->getUri()->getPath() == '/v1/projects/project-id/serviceAccounts/team-my-team@project-id.iam.gserviceaccount.com/keys';
        }), [])->willReturn(new Response(200, [], \GuzzleHttp\json_encode([
            'name' => 'name-of-the-key',
            'private_key_data' => $createdServiceAccount
        ])));

        $encodedServiceAccount = base64_encode(json_encode([
            'type' => 'service-account',
            'client_id' => '12345',
            'client_secret' => 'qwerty',
        ]));

        $containerEngineClusterRepository->find(
            Argument::that(function(GoogleAccount $account) use ($encodedServiceAccount) {
                return $account->getServiceAccount() == $encodedServiceAccount;
            }),
            'project-id',
            'cluster-identifier'
        )->willReturn(new ContainerEngineCluster(
            'name-of-the-cluster',
            'eu-west-1',
            '1.2.3.4',
            null,
            new ContainerEngineCluster\MasterAuthentication(
                'master-username',
                'master-password',
                'ca-cert',
                'client-cert',
                'client-key'
            ),
            '1.8',
            2
        ));

        $slug =
            'gke://service-account:'.$encodedServiceAccount.'@project-id/cluster-identifier'
        ;

        $cluster = $this->createForTeam(new Team('my-team', 'Team'), 'cluster', $slug);
        $cluster->getIdentifier()->shouldBe('cluster');
        $cluster->getAddress()->shouldBe('https://1.2.3.4');
        $cluster->getCredentials()->getGoogleCloudServiceAccount()->shouldBe($createdServiceAccount);
        $cluster->getManagementCredentials()->getUsername()->shouldBe('master-username');
        $cluster->getManagementCredentials()->getPassword()->shouldBe('master-password');
    }
}
