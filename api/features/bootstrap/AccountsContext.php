<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfile;
use ContinuousPipe\Billing\BillingProfile\UserBillingProfileRepository;
use ContinuousPipe\Security\Account\Account;
use ContinuousPipe\Security\Account\AccountRepository;
use ContinuousPipe\Security\Account\GitHubAccount;
use ContinuousPipe\Security\Account\GoogleAccount;
use ContinuousPipe\Security\Team\Team;
use ContinuousPipe\Security\User\User;
use GuzzleHttp\PredefinedRequestMappingMiddleware;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class AccountsContext implements Context
{
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * @var PredefinedRequestMappingMiddleware
     */
    private $predefinedRequestMappingMiddleware;

    /**
     * @var UserBillingProfileRepository
     */
    private $userBillingProfileRepository;

    /**
     * @var \SecurityContext
     */
    private $securityContext;

    public function __construct(
        AccountRepository $accountRepository,
        KernelInterface $kernel,
        PredefinedRequestMappingMiddleware $predefinedRequestMappingMiddleware,
        UserBillingProfileRepository $userBillingProfileRepository
    ) {
        $this->accountRepository = $accountRepository;
        $this->kernel = $kernel;
        $this->predefinedRequestMappingMiddleware = $predefinedRequestMappingMiddleware;
        $this->userBillingProfileRepository = $userBillingProfileRepository;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->securityContext = $scope->getEnvironment()->getContext('SecurityContext');
    }

    /**
     * @Given the Google account :account have the following Google Compute projects:
     */
    public function theGoogleAccountHaveTheFollowingGoogleComputeProjects($account, TableNode $table)
    {
        $this->predefinedRequestMappingMiddleware->addMapping([
            'method' => 'GET',
            'path' => '#^/v1beta1/projects$#',
            'response' => new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                'projects' => $table->getHash(),
            ]))
        ]);
    }

    /**
     * @Given there is a billing profile :uuid for the user :username
     */
    public function thereIsABillingProfileForTheUser($uuid, $username)
    {
        $this->userBillingProfileRepository->save(new UserBillingProfile(
            Uuid::fromString($uuid),
            $this->securityContext->thereIsAUser($username)->getUser(),
            'NAME'
        ));
    }

    /**
     * @Given the team :team is linked to the billing profile :billingProfileUuid
     */
    public function theTeamIsLinkedToTheBillingProfile($team, $billingProfileUuid)
    {
        $this->userBillingProfileRepository->link(
            new Team($team, $team),
            $this->userBillingProfileRepository->find(Uuid::fromString($billingProfileUuid))
        );
    }

    /**
     * @When I request the list of Google project for the account :account
     */
    public function iRequestTheListOfGoogleProjectForTheAccount($account)
    {
        $this->response = $this->kernel->handle(Request::create('/api/accounts/' . $account . '/google/projects'));
    }

    /**
     * @When I request my billing profile
     */
    public function iRequestMyBillingProfile()
    {
        $this->response = $this->kernel->handle(Request::create('/api/me/billing-profile'));
    }

    /**
     * @Then I should be forbidden to see this account
     */
    public function iShouldBeForbiddenToSeeThisAccount()
    {
        if ($this->response->getStatusCode() != \Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN) {
            throw new \RuntimeException(sprintf('Expected status code %d but got %d', \Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN, $this->response->getStatusCode()));
        }
    }

    /**
     * @Then I should see the project :projectId
     */
    public function iShouldSeeTheProject($projectId)
    {
        $this->assertResponseCode(200);

        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);
        $matchingProjects = array_filter($json, function(array $project) use ($projectId) {
            return $project['projectId'] == $projectId;
        });

        if (count($matchingProjects) == 0) {
            throw new \RuntimeException('No matching project found');
        }
    }

    /**
     * @Then I should see the billing profile :uuid
     */
    public function iShouldSeeTheBillingProfile($uuid)
    {
        $this->assertResponseCode(200);
        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);
        if ($json['uuid'] != $uuid) {
            throw new \RuntimeException(sprintf('Found UUID %s while expecting %s', $json['uuid'], $uuid));
        }
    }

    /**
     * @Then I should see the billing profile to be not found
     */
    public function iShouldSeeTheBillingProfileToBeNotFound()
    {
        $this->assertResponseCode(404);
    }

    /**
     * @Given the project :project have the following zones:
     */
    public function theProjectHaveTheFollowingZones($project, TableNode $table)
    {
        $this->predefinedRequestMappingMiddleware->addMapping([
            'method' => 'GET',
            'path' => '#/projects/' . $project . '/zones$#',
            'response' => new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                'items' => array_map(function(array $zone) {
                    return array_merge([
                        'kind' => 'compute#zone',
                        'id' => '2222',
                        'creationTimestamp' => '2014-07-15T10:44:08.663-07:00',
                        'name' => 'asia-east1-c',
                        'description' => 'asia-east1-c',
                        'status' => 'UP',
                        'region' => 'https://www.googleapis.com/compute/beta/projects/continuous-pipe-1042/regions/asia-east1',
                        'selfLink' => 'https://www.googleapis.com/compute/beta/projects/continuous-pipe-1042/regions/asia-east1-c',
                    ], $zone);
                }, $table->getHash()),
            ]))
        ]);
    }

    /**
     * @Given there is a project :project in the account :account
     */
    public function thereIsAProjectInTheAccount($project, $account)
    {
    }

    /**
     * @Given there is a cluster named :clusterName in the :zone zone in the project :project
     */
    public function thereIsAClusterNamedInTheZoneInTheProject($clusterName, $zone, $project)
    {
        $cluster = \GuzzleHttp\json_decode('{"name":"builder-eu-west1-b","nodeConfig":{"machineType":"n1-standard-2","diskSizeGb":100,"oauthScopes":["https://www.googleapis.com/auth/compute","https://www.googleapis.com/auth/devstorage.read_only","https://www.googleapis.com/auth/service.management","https://www.googleapis.com/auth/servicecontrol","https://www.googleapis.com/auth/logging.write","https://www.googleapis.com/auth/monitoring"],"imageType":"GCI","serviceAccount":"default"},"masterAuth":{"username":"admin","password":"ATOXm3tRzh0NuuBi","clusterCaCertificate":"LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tCk1JSUROekNDQWgrZ0F3SUJBZ0lRRHdMV096dXF2WFNtV2dYcWRNNGNhakFOQmdrcWhraUc5dzBCQVFzRkFEQkYKTVVNd1FRWURWUVFERERwbGRYSnZjR1V0ZDJWemRERXRZaTB4TURFMU5ERTJNRGc0TXprM0xXSjFhV3hrWlhJdApaWFV0ZDJWemRERXRZa0F4TkRZNU9UVXpOVFF3TUI0WERURTJNRGN6TVRBNE1qVTBNRm9YRFRJeE1EY3pNREE0Ck1qVTBNRm93UlRGRE1FRUdBMVVFQXd3NlpYVnliM0JsTFhkbGMzUXhMV0l0TVRBeE5UUXhOakE0T0RNNU55MWkKZFdsc1pHVnlMV1YxTFhkbGMzUXhMV0pBTVRRMk9UazFNelUwTURDQ0FTSXdEUVlKS29aSWh2Y05BUUVCQlFBRApnZ0VQQURDQ0FRb0NnZ0VCQU4vUCszMmo3VFM1OHJtUWpUd3F0UTU4cTN5QVRuUGUzMEZSUlZJcmZuMVZaQklICll2bVJNTTQydHQwRHJmQjlMZzJkcDkvOG5YcXlCaGQvZlZnZmlxbjlhWUY3ZDFKYklBb0tEdDBsN0F3U0dJV3cKSUNSaWRld0dLNDdtVGF6aWIybWh0NVdhRmhZSFVmQlVCT1MxdzFKSjk5YVQxOVlaT2pQTHRaa214T3ZGUGVKVgo2cVFteHdxYVlPbStDanZCL0YwVmhoWHZLTkowVUIrNVdyeFFicDBKS0d5azBoMmN2Nk5QMTRKR2Y4cmJmUFd4CmFFcXFHZ2pCRFV2NHZKSTFLaVlrWWpCOFRoY2Ivcm80YmtwOUFoSkVWTXNHSFk2aHR0ajhvZkVFcklZNytQalYKZmdEWDdFb2NwUnVIbHdySVhTdXlpNFQ0NzFQaE0vcVk2U3hoQzhjQ0F3RUFBYU1qTUNFd0RnWURWUjBQQVFILwpCQVFEQWdJRU1BOEdBMVVkRXdFQi93UUZNQU1CQWY4d0RRWUpLb1pJaHZjTkFRRUxCUUFEZ2dFQkFGbEwrdUc4CnNMd29NUk1zSS8rUE1hSVBrVjUrS1A2dHFBWTR4WFhBL0JGTm9ZU2o1eXlDY3pUc2ZqNkhzN1lFd3RSTUJZcFUKaG5YdldtbTlVOTVZMHpIcjJ0L2JpelhCMGlsOHRNWk04cG9UaXhVbjg5cUZiM3FiRGpBT0M3ays5RlU4dmtjVQpGbWNNcm8vbCszbUU4Uzd0SEd0Znh3TDlZL2NZSWZoVVNabHByNnRJSTZmVnZZY2dZeE4vTVhVUit1a0J3WjE5CmRLRGhFMHdZMTVyeHJ1cTRNQUl6MC82RURXZHA1NG55T2x5OXVVcWZUVVJYUnNQR0xhSGEzeTIrcGRFa1U4alQKUWhhd3F1ZlpBK1lYbnBhOEF4d2FzdjdRQXE5UjlsZFkxZjBleTFPdEQwL3NIZlFSSjFEYkZlNno4THdyNy9EdAoxL1J6S2ZzSyt5MjJBY1E9Ci0tLS0tRU5EIENFUlRJRklDQVRFLS0tLS0K","clientCertificate":"LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tCk1JSURGVENDQWYyZ0F3SUJBZ0lRY1ZMVXk2TWwxYlpJVWFIak00cXVNREFOQmdrcWhraUc5dzBCQVFzRkFEQkYKTVVNd1FRWURWUVFERERwbGRYSnZjR1V0ZDJWemRERXRZaTB4TURFMU5ERTJNRGc0TXprM0xXSjFhV3hrWlhJdApaWFV0ZDJWemRERXRZa0F4TkRZNU9UVXpOVFF3TUI0WERURTJNRGN6TVRBNE1qVTBNbG9YRFRJeE1EY3pNREE0Ck1qVTBNbG93RVRFUE1BMEdBMVVFQXhNR1kyeHBaVzUwTUlJQklqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FROEEKTUlJQkNnS0NBUUVBcXlYK3hLN1FHbGdGeWxvTC9KWVh6VXhUdUgwSmE2VVhJb3lvOEFxMU5IRmxVRUlHcEdlMApVZW5RTjZ5aXRJbmw2a1VaNVY4eWRpMm5HMWhLKzMxeUUvQVRGTkpiK2VEZXJkTzlDYUtuRXBXb1J3REhZUXZMClNQc1ZiNCtCaFZ0SEZpTzI3VGxwMEptV2lOdGpIWnJVRWxaR1NjN3R6OFlRWmRqM3F1YUJqcnpGL0VWR2VGL3YKYWNHS2ViVGc4cWVrZllPQTR5N1Mwd3MzanE0b25ZbE1pVWV1Rithc294MlpDb3NZbXhwNTk4WDFUcUhUUFJmeQo2cUlOM01lTlRxejVhOHcxMEJ1dTlKTFVWOWMyaC9nanVxUThPNytqeTRvd2J6SlRyaUxiN1hHM21MZTdSVWNvCmdUaU9OeUJSa3piSW1UcGNYbGZJdEREWkRaaytHbmdWM1FJREFRQUJvelV3TXpBT0JnTlZIUThCQWY4RUJBTUMKQmFBd0V3WURWUjBsQkF3d0NnWUlLd1lCQlFVSEF3SXdEQVlEVlIwVEFRSC9CQUl3QURBTkJna3Foa2lHOXcwQgpBUXNGQUFPQ0FRRUEzdzhxWDRmb3RoMENuQ0tzT0pQQjBET28rMmpNRmM1QTdhb2ZhT1ZwNWxvcjUxU1RRa3Y5CjJ4dGQ3QUtVZFZuVkJmaWF4QkQ2SWhwOXFNS05rQ1h4TS9ld1BzcGZEak01SEVQSG5CMHB3LzNlZTdMcy9IL3oKUE5KS1RneFpmSHJFaFVkeVlKcE8vZlpuOTVaNHZWQTIwRkRXcmZocUpwTEVuL1pTWnZpRjg5ZzhjV1IycGJmUQp5ZFJKNTNaZ1RSWVNUZ2lkR0Jqc0hyTGNXdythV1BqbFQzUEVjMVZKN21ML2JCa1JpcXNwMmUrZXR0Uld1NXQ4CmRLRjcveDdnbDh4QmF1d0Fla3g5Ky9CVzgvTmVZOEpBbUpId3l1dmlHTkc3d25JYXBPOWd5K1dCelM1ak1JZCsKSnVES0VyYWU5clQweVU4NnB6TncrNVRjYXB6MGNhU1B5UT09Ci0tLS0tRU5EIENFUlRJRklDQVRFLS0tLS0K","clientKey":"LS0tLS1CRUdJTiBSU0EgUFJJVkFURSBLRVktLS0tLQpNSUlFb2dJQkFBS0NBUUVBcXlYK3hLN1FHbGdGeWxvTC9KWVh6VXhUdUgwSmE2VVhJb3lvOEFxMU5IRmxVRUlHCnBHZTBVZW5RTjZ5aXRJbmw2a1VaNVY4eWRpMm5HMWhLKzMxeUUvQVRGTkpiK2VEZXJkTzlDYUtuRXBXb1J3REgKWVF2TFNQc1ZiNCtCaFZ0SEZpTzI3VGxwMEptV2lOdGpIWnJVRWxaR1NjN3R6OFlRWmRqM3F1YUJqcnpGL0VWRwplRi92YWNHS2ViVGc4cWVrZllPQTR5N1Mwd3MzanE0b25ZbE1pVWV1Rithc294MlpDb3NZbXhwNTk4WDFUcUhUClBSZnk2cUlOM01lTlRxejVhOHcxMEJ1dTlKTFVWOWMyaC9nanVxUThPNytqeTRvd2J6SlRyaUxiN1hHM21MZTcKUlVjb2dUaU9OeUJSa3piSW1UcGNYbGZJdEREWkRaaytHbmdWM1FJREFRQUJBb0gvWVBBa1hVS21uRVUvQWwzKwpiQktYYUxEU3Vxd1hxZURZT2JseDlvUWFIcG9ieUZtZGFZRlRvUkhOM2JycWJWZXQ0Z05CcDZsRDY2dnYrbzBICjYyb2lNeWpIcGdPQUZRaEpHQ3ZWNXA4NkFrekNBM1Z0ZUlvMW1pQ2RBNU5FeVVQcC82QTYvQ0tJeko0eHBWS2QKMFNiZzk0SG1UZWZteXNoa2dVdGkvR21TK0VVR1dMQW9JdHhmbW5ETEliVmdUMWdWSUlTOC9tcXE4OHlOeFVTOQpDaXJkUXJreUZvM3VJNlREZzFCOXlLb2lMVnloeDAxQnBZUVZwZmkwTUwxNm5nU0lEZklQTXVRRmt1dGoxK1FHCjNnUW9PVVpXK3pqR2c2aThVb2pocFFJeGNma3o4VFpQMnBuaUlIWmZpeFFEeEk5d2E3NDQxWkk2TlhpMzdDMGEKSURqZEFvR0JBTVZyZXpRdVBSUTdPTXF0U3p2NWo4WEFnaGtGZktjNlpvMzM0cUdVTXFVdmtVS0V0aGVKY0wzawo5WElyZ2dyMXRseDFMOEhnajlIdU9YbHRHZEVPYUdTeHE1ejNIZDZTWUpIUFF5Yk85OVY2bHYzU0Vzc1dtVjdhCldBUVVLNGUwMWkzc1QyeTdNR1pvVnlYVU8ranVveUYvNDFrdDA1TkdhM3plenZSZ1A5dUxBb0dCQU4zdTNsNkUKWGZ2b1BlV2hKMnh4cEdzbDRCbEhXbjNza0ZraWxhbk5laUl1VFhMNFEvaUNsOWRXNFk5UGluU1ZmV1ZtemRBZwo1L0VHNXhORWhiWlN6OHRVcHBOTDFwVk5IU01zUnB1Z05UcG41V2hWUTFoS1BnOW9QTkh1NTduUjZkZUJUV0FUCjgrbHJQOXptZ2xIMmluV3lua04ra0hWZ0xhZG5lUWZldVNFM0FvR0FhWEFnR3h4ZTdzRTZjYlRnSzZYOERZZmwKYyt1a1NjUTlKYkd3enM5UnhUdUVmMXhWekhoUlNIcFNSS25NQ0lKMjVTYUpYU2pNWnppdVpaWEpaZ2dsNVRHbgpFR3hDL2E1NytTUVRIMVVHdEhPRzFRVXZtRnYzaWR0ZmlyNGpDWldobG1GUmdpYnZrS0pGZVNUQzRvTWhpVEMrCmdkQ2g0VlNJNytZbjdnakIwa1VDZ1lFQTE1TE9aMHIySzlvVVBiaERIaUJwRURjekxmclVXSnJ3UDlUTFFhdzQKNVhqS2ZGSFJYRlFsLytNQnFINGZ4RXp0Q0JGSys4N3EzWUhSOVRKTEc2WG05OS9iQ2hyUmJpY0FsWWpOY1IrMgpkR3cxTnhvVEYzRE9SWkwvK1AreUVScG9wWStReERHOWJFOWtNa09wOU1taEJ1Q2d3SGp4QTBLUU5oclpRcDdDCmZ4TUNnWUVBblR3NHF4ZGRseWMwZkREem1KQ0ZXd1ZEZ09RQjZQZktDQ2JkWmxQNTh2TFc5RnVJVmhwbVE3N1IKZjljVzlUd2pXUGVDbmZCWk9nNGNZVkhkRnFNQTZhVEhjMU9FcFJvR3VUblBhWHZIVHNPRTRqMm03cjJoM1liSgpXMDh1cjdmTmtvTGU3YXY1YWdyaGVWYkZ5UEM3eXdMQUluZWJUMklrenY2T2xEbmtORTQ9Ci0tLS0tRU5EIFJTQSBQUklWQVRFIEtFWS0tLS0tCg=="},"loggingService":"logging.googleapis.com","monitoringService":"monitoring.googleapis.com","network":"default","clusterIpv4Cidr":"10.148.0.0/14","nodePools":[{"name":"pool-2-cpus","config":{"machineType":"n1-standard-2","diskSizeGb":100,"oauthScopes":["https://www.googleapis.com/auth/compute","https://www.googleapis.com/auth/devstorage.read_only","https://www.googleapis.com/auth/service.management","https://www.googleapis.com/auth/servicecontrol","https://www.googleapis.com/auth/logging.write","https://www.googleapis.com/auth/monitoring"],"imageType":"GCI","serviceAccount":"default"},"initialNodeCount":1,"autoscaling":{"enabled":true,"minNodeCount":1,"maxNodeCount":2},"selfLink":"https://container.googleapis.com/v1/projects/continuous-pipe-1042/zones/europe-west1-b/clusters/builder-eu-west1-b/nodePools/pool-2-cpus","version":"1.4.5","instanceGroupUrls":["https://www.googleapis.com/compute/v1/projects/continuous-pipe-1042/zones/europe-west1-b/instanceGroupManagers/gke-builder-eu-west1-b-pool-2-cpus-ca01d004-grp"],"status":"RUNNING"}],"locations":["europe-west1-b"],"selfLink":"https://container.googleapis.com/v1/projects/continuous-pipe-1042/zones/europe-west1-b/clusters/builder-eu-west1-b","zone":"europe-west1-b","endpoint":"104.155.47.227","initialClusterVersion":"1.3.3","currentMasterVersion":"1.4.5","currentNodeVersion":"1.4.5","createTime":"2016-07-31T08:25:27+00:00","status":"RUNNING","nodeIpv4CidrSize":24,"servicesIpv4Cidr":"10.151.240.0/20","instanceGroupUrls":["https://www.googleapis.com/compute/v1/projects/continuous-pipe-1042/zones/europe-west1-b/instanceGroupManagers/gke-builder-eu-west1-b-pool-2-cpus-ca01d004-grp"],"currentNodeCount":1}', true);
        $cluster['name'] = $clusterName;

        $this->predefinedRequestMappingMiddleware->addMapping([
            'method' => 'GET',
            'path' => '#/projects/' . $project . '/zones/' . $zone . '/clusters$#',
            'response' => new \GuzzleHttp\Psr7\Response(200, [], json_encode([
                'clusters' => [
                    $cluster,
                ],
            ]))
        ]);
    }

    /**
     * @When I request the list of the clusters for the account :account and the project :project
     */
    public function iRequestTheListOfTheClustersForTheAccountAndTheProject($account, $project)
    {
        $this->response = $this->kernel->handle(Request::create('/api/accounts/' . $account . '/google/projects/' . $project .'/clusters'));
    }

    /**
     * @Then I should see the cluster :clusterName
     */
    public function iShouldSeeTheCluster($clusterName)
    {
        if ($this->response->getStatusCode() != 200) {
            echo $this->response->getContent();

            throw new \RuntimeException('Expected to see response 200');
        }

        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);
        $matchingClusters = array_filter($json, function(array $cluster) use ($clusterName) {
            return $cluster['name'] == $clusterName;
        });

        if (count($matchingClusters) == 0) {
            throw new \RuntimeException('No matching project found');
        }
    }

    /**
     * @Given I have a connected GitHub account :uuid for the user :username
     * @Given there is a connected GitHub account :uuid for the user :username
     */
    public function iHaveAConnectedGithubAccountForTheUser($uuid, $username)
    {
        $this->accountRepository->link($username, new GitHubAccount(
            $uuid,
            '1234567',
            $username,
            'token'
        ));
    }

    /**
     * @Given there is connected Google account :uuid for the user :username
     */
    public function thereIsConnectedGoogleAccountForTheUser($uuid, $username)
    {
        $this->accountRepository->link($username, new GoogleAccount(
            $uuid,
            $username,
            $username.'@example.com',
            'REFRESH_TOKEN'
        ));
    }

    /**
     * @When I request the list of my accounts
     */
    public function iRequestTheListOfMyAccounts()
    {
        $this->response = $this->kernel->handle(Request::create('/api/me/accounts'));
    }


    /**
     * @When I request the details of the account :uuid
     */
    public function iRequestTheDetailsOfTheAccount($uuid)
    {
        $this->response = $this->kernel->handle(Request::create('/api/accounts/'.$uuid));
    }

    /**
     * @Then I should see the details of the account :uuid
     */
    public function iShouldSeeTheDetailsOfTheAccount($uuid)
    {
        $this->assertResponseCode(200);
        $json = \GuzzleHttp\json_decode($this->response->getContent(), true);

        if ($json['uuid'] != $uuid) {
            throw new \RuntimeException('Account cannot be found body');
        }
    }

    /**
     * @Then I should see the :type account :uuid
     */
    public function iShouldSeeTheGithubAccount($type, $uuid)
    {
        if (null === $this->findAccountInResponse($this->response, $type, $uuid)) {
            throw new \RuntimeException('Account not found');
        }
    }

    /**
     * @Then I should not see the :type account :uuid
     */
    public function iShouldNotSeeTheGoogleAccount($type, $uuid)
    {
        if (null !== $this->findAccountInResponse($this->response, $type, $uuid)) {
            throw new \RuntimeException('Account found');
        }
    }

    /**
     * @When I unlink the account :uuid from the user :username
     */
    public function iUnlinkTheAccountFromTheUser($uuid, $username)
    {
        $this->accountRepository->unlink(
            $username,
            $this->accountRepository->find($uuid)
        );
    }

    /**
     * @Then the account :uuid should not be linked to the user :username
     */
    public function theAccountShouldNotBeLinkedToTheUser($uuid, $username)
    {
        $matchingAccounts = array_filter($this->accountRepository->findByUsername($username), function(Account $account) use ($uuid) {
            return $account->getUuid() == $uuid;
        });

        if (count($matchingAccounts) != 0) {
            throw new \RuntimeException('Found matching account');
        }
    }

    /**
     * @Then the user :username should be linked to a GitHub account with username :gitHubAccountUsername
     */
    public function theUserShouldBeLinkedToAGithubAccountWithUsername($username, $gitHubAccountUsername)
    {
        $accounts = $this->accountRepository->findByUsername($username);
        $matchingAccounts = array_filter($accounts, function(Account $account) use ($gitHubAccountUsername) {
            if (!$account instanceof GitHubAccount) {
                return false;
            }

            return $account->getUsername() == $gitHubAccountUsername;
        });

        if (count($matchingAccounts) == 0) {
            throw new \RuntimeException('Account is not found');
        }
    }

    /**
     * @Then the billing profile of the team :slug should be :billingProfileUuid
     */
    public function theBillingProfileOfTheTeamShouldBe($slug, $billingProfileUuid)
    {
        $billingProfile = $this->userBillingProfileRepository->findByTeam(new Team($slug, $slug));

        if (!$billingProfile->getUuid()->equals(Uuid::fromString($billingProfileUuid))) {
            throw new \RuntimeException(sprintf(
                'Found %s while expecting %s',
                $billingProfile->getUuid(),
                $billingProfileUuid
            ));
        }
    }

    private function findAccountInResponse(Response $response, $type, $uuid)
    {
        if ($response->getStatusCode() != 200) {
            echo $response->getContent();

            throw new \RuntimeException(sprintf('Expected status code 200, got %d', $response->getStatusCode()));
        }

        $json = json_decode($response->getContent(), true);
        if (!is_array($json)) {
            throw new \RuntimeException('Unexpected non-JSON resposne');
        }

        $type = strtolower($type);
        $matchingAccount = array_filter($json, function(array $account) use ($type, $uuid) {
            return $account['type'] == $type && $account['uuid'] == $uuid;
        });

        return current($matchingAccount) ?: null;
    }

    /**
     * @param $expectedStatus
     */
    private function assertResponseCode($expectedStatus)
    {
        if ($this->response->getStatusCode() != $expectedStatus) {
            echo $this->response->getContent();

            throw new \RuntimeException(sprintf('Got status %d while expected to see %d', $this->response->getStatusCode(), $expectedStatus));
        }
    }
}
