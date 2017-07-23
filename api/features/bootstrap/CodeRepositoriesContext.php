<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use ContinuousPipe\Archive\FileSystemArchive;
use Behat\Gherkin\Node\PyStringNode;
use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\CodeRepositoryUser;
use ContinuousPipe\River\CodeRepository\Event\BranchDeleted;
use ContinuousPipe\River\CodeRepository\Event\CodePushed;
use ContinuousPipe\River\CodeRepository\Event\PullRequestOpened;
use ContinuousPipe\River\CodeRepository\FileSystem\LocalFilesystemResolver;
use ContinuousPipe\River\CodeRepository\FileSystem\PartiallyOverwrittenFileSystemResolver;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\CodeRepository\InMemoryBranchQuery;
use ContinuousPipe\River\CodeRepository\OverwrittenArchiveStreamer;
use ContinuousPipe\River\CodeRepository\PullRequest;
use ContinuousPipe\River\Event\GitHub\CommentedTideFeedback;
use ContinuousPipe\River\Event\GitHub\PullRequestClosed;
use ContinuousPipe\River\EventBus\EventStore;
use ContinuousPipe\River\Notifications\Events\CommentedPullRequest;
use ContinuousPipe\River\Tests\CodeRepository\PredictableCommitResolver;
use GuzzleHttp\Psr7\Stream;
use Ramsey\Uuid\Uuid;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CodeRepositoriesContext implements Context
{
    /**
     * @var \TideContext
     */
    private $tideContext;

    /**
     * @var \FlowContext
     */
    private $flowContext;

    /**
     * @var PredictableCommitResolver
     */
    private $predictableCommitResolver;

    /**
     * @var MessageBus
     */
    private $eventBus;

    /**
     * @var EventStore
     */
    private $eventStore;
    /**
     * @var InMemoryBranchQuery
     */
    private $branchQuery;
    /**
     * @var LocalFilesystemResolver
     */
    private $localFilesystemResolver;
    /**
     * @var OverwrittenArchiveStreamer
     */
    private $overwrittenArchiveStreamer;
    /**
     * @var PartiallyOverwrittenFileSystemResolver
     */
    private $partiallyOverwrittenFileSystemResolver;

    public function __construct(
        PredictableCommitResolver $predictableCommitResolver,
        MessageBus $eventBus,
        EventStore $eventStore,
        InMemoryBranchQuery $branchQuery,
        LocalFilesystemResolver $localFilesystemResolver,
        OverwrittenArchiveStreamer $overwrittenArchiveStreamer,
        PartiallyOverwrittenFileSystemResolver $partiallyOverwrittenFileSystemResolver
    ) {
        $this->predictableCommitResolver = $predictableCommitResolver;
        $this->eventBus = $eventBus;
        $this->eventStore = $eventStore;
        $this->branchQuery = $branchQuery;
        $this->localFilesystemResolver = $localFilesystemResolver;
        $this->overwrittenArchiveStreamer = $overwrittenArchiveStreamer;
        $this->partiallyOverwrittenFileSystemResolver = $partiallyOverwrittenFileSystemResolver;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->tideContext = $scope->getEnvironment()->getContext('TideContext');
        $this->flowContext = $scope->getEnvironment()->getContext('FlowContext');
    }

    /**
     * @Given the code repository contains the fixtures folder :fixtureFolder
     */
    public function theCodeRepositoryContainsTheFixturesFolder($fixtureFolder)
    {
        $this->localFilesystemResolver->overwriteFileSystemWithLocalPath(__DIR__.'/../fixtures/'.$fixtureFolder);
    }

    /**
     * @Given the :path file in the code repository contains:
     */
    public function theFileInTheCodeRepositoryContains($path, PyStringNode $contents)
    {
        $this->partiallyOverwrittenFileSystemResolver->overrideFile($path, $contents->getRaw());
    }

    /**
     * @Given the head commit of branch :branch is :sha1
     */
    public function theHeadCommitOfBranchIs($branch, $sha1)
    {
        $this->predictableCommitResolver->headOfBranchIs($branch, $sha1);
    }

    /**
     * @When the commit :sha1 is pushed to the branch :branch
     * @When the commit :sha1 was pushed to the branch :branch
     */
    public function theCommitIsPushedToTheBranch($sha1, $branch)
    {
        $flow = $this->flowContext->getCurrentFlow();

        $this->eventBus->handle(
            new CodePushed(
                $flow->getUuid(),
                new CodeReference(
                    $flow->getCodeRepository(),
                    $sha1,
                    $branch
                ),
                [
                    new CodeRepositoryUser(
                        $flow->getUser()->getUsername(),
                        $flow->getUser()->getEmail()
                    ),
                ]
            )
        );
    }

    /**
     * @Given a comment identified :commentId was already added
     */
    public function aCommentIdentifiedWasAlreadyAdded($commentId)
    {
        $this->eventStore->add(new CommentedPullRequest(
            $this->tideContext->getCurrentTideUuid(),
            new PullRequest(1234),
            $commentId
        ));
    }

    /**
     * @Given there is a :branch branch in the repository for the flow :flow
     */
    public function thereIsABranchInTheRepository($branch, $flow)
    {
        $this->branchQuery->addBranch($flow, $branch);
    }

    /**
     * @When the branch :branch is deleted for the repository for the flow :flow
     */
    public function theBranchIsDeleted($branch, $flow)
    {
        $this->eventBus->handle(new BranchDeleted(Uuid::fromString($flow), new CodeReference(new GitHubCodeRepository('a', 'b', 'c', 'd', true), null, $branch)));
    }

    /**
     * @When I open a pull request :number titled :title for commit :commit the branch :branch for the flow :flow
     */
    public function iOpenAPullRequestTitledForCommitTheBranch($number, $title, $commit, $branch, $flow)
    {
        $this->eventBus->handle(new PullRequestOpened(
            Uuid::fromString($flow), 
            new CodeReference(new GitHubCodeRepository('a', 'b', 'c', 'd', true), $commit, $branch),
            new PullRequest($number, $title))
        );
    }

    /**
     * @When I close the pull request :number titled :title for commit :commit of the branch :branch for the flow :flow
     */
    public function iCloseThePullRequestTitledForCommitOfTheBranchForTheFlow($number, $title, $commit, $branch, $flow)
    {
        $this->eventBus->handle(new PullRequestClosed(
            Uuid::fromString($flow),
            new CodeReference(new GitHubCodeRepository('a', 'b', 'c', 'd', true), $commit, $branch),
            new PullRequest($number, $title))
        );
    }

    /**
     * @Given the code archive of the flow :flowUuid looks like the fixtures file :archiveFile
     */
    public function theCodeArchiveOfTheFlowLooksLikeTheFixturesFile($flowUuid, $archiveFile)
    {
        $this->overwrittenArchiveStreamer->overwriteForFlow(
            $flowUuid,
            function() use ($archiveFile) {
                return new Stream(fopen(__DIR__.'/../fixtures/'.$archiveFile, 'r'));
            }
        );
    }

    /**
     * @Then the archive should not contain a :fileName file
     */
    public function theArchiveShouldNotContainAFile($fileName)
    {
        if ($this->archiveFromFlowResponse()->contains($fileName)) {
            throw new \RuntimeException('The file should not exists');
        }
    }

    /**
     * @Then the archive should contain a :fileName file
     */
    public function theArchiveShouldContainAFile($fileName)
    {
        $archive = $this->archiveFromFlowResponse();
        if (!$archive->contains($fileName)) {
            throw new \RuntimeException('The file do not exists');
        }
    }

    /**
     * @Then the file :filePath in the archive should look like:
     */
    public function theFileInTheArchiveShouldLookLike($filePath, PyStringNode $string)
    {
        $archive = $this->archiveFromFlowResponse();
        $foundContents = $archive->getFilesystem()->getContents($filePath);
        $expectedContents = $string->getRaw();

        if ($foundContents != $expectedContents) {
            throw new \RuntimeException('Found following content instead: '.$foundContents);
        }
    }

    private function archiveFromFlowResponse()
    {
        if (null === ($archiveResponse = $this->flowContext->getResponse())) {
            throw new \RuntimeException('Flow response empty, did you run the right scenario before?');
        }

        var_dump($archiveResponse->getContent());
        if ($archiveResponse->getStatusCode() != 200) {

            throw new \RuntimeException(sprintf('Expected status code 200 but got %d', $archiveResponse->getStatusCode()));
        }

        return FileSystemArchive::fromStream(
            \GuzzleHttp\Psr7\stream_for(
                $archiveResponse->getContent()
            ),
            FileSystemArchive::TAR_GZ
        );
    }
}
