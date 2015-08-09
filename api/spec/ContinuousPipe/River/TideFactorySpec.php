<?php

namespace spec\ContinuousPipe\River;

use ContinuousPipe\River\CodeReference;
use ContinuousPipe\River\CodeRepository\GitHub\GitHubCodeRepository;
use ContinuousPipe\River\Flow;
use ContinuousPipe\River\Tide;
use ContinuousPipe\User\User;
use GitHub\WebHook\Model\Repository;
use LogStream\Log;
use LogStream\Logger;
use LogStream\LoggerFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TideFactorySpec extends ObjectBehavior
{
    function let(LoggerFactory $loggerFactory)
    {
        $this->beConstructedWith($loggerFactory);
    }

    function it_should_create_a_logger(LoggerFactory $loggerFactory, Logger $logger, Log $log)
    {
        $repository = new GitHubCodeRepository(new Repository('foo', 'http://github.com/foo/bar'));
        $flow = Flow::fromUserAndCodeRepository(new User('my@ema.l'), $repository);
        $codeReference = new CodeReference($repository, '1234');

        $logger->getLog()->willReturn($log);
        $loggerFactory->create()->shouldBeCalled()->willReturn($logger);
        $this->create($flow, $codeReference)->shouldReturnAnInstanceOf(Tide::class);
    }
}
