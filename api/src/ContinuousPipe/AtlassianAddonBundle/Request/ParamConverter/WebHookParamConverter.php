<?php

namespace ContinuousPipe\AtlassianAddonBundle\Request\ParamConverter;

use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\BuildStatusCreated;
use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\BuildStatusUpdated;
use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\CommentEvent;
use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\PullRequestApproved;
use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\PullRequestCommentCreated;
use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\PullRequestCommentDeleted;
use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\PullRequestCommentUpdated;
use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\PullRequestCreated;
use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\PullRequestDeclined;
use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\PullRequestMerged;
use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\PullRequestUnapproved;
use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\PullRequestUpdated;
use ContinuousPipe\AtlassianAddon\BitBucket\WebHook\Push;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class WebHookParamConverter implements ParamConverterInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var array
     */
    private $eventMapping;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
        $this->eventMapping = [
            'repo:push' => Push::class,
            'repo:commit_comment_created' => CommentEvent::class,
            'repo:commit_status_updated' => BuildStatusUpdated::class,
            'repo:commit_status_created' => BuildStatusCreated::class,
            'pullrequest:created' => PullRequestCreated::class,
            'pullrequest:updated' => PullRequestUpdated::class,
            'pullrequest:approved' => PullRequestApproved::class,
            'pullrequest:unapproved' => PullRequestUnapproved::class,
            'pullrequest:fulfilled' => PullRequestMerged::class,
            'pullrequest:rejected' => PullRequestDeclined::class,
            'pullrequest:comment_created' => PullRequestCommentCreated::class,
            'pullrequest:comment_updated' => PullRequestCommentUpdated::class,
            'pullrequest:comment_deleted' => PullRequestCommentDeleted::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        try {
            $decoded = \GuzzleHttp\json_decode($request->getContent(), true);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException('Unable to decode the JSON', $e);
        }

        if (!isset($decoded['event'])) {
            throw new BadRequestHttpException('Unable to identify the event');
        } elseif (!isset($this->eventMapping[$decoded['event']])) {
            throw new BadRequestHttpException(sprintf(
                'The event "%s" is not understood by the add-on',
                $decoded['event']
            ));
        }

        $request->attributes->set(
            $configuration->getName(),
            $this->serializer->deserialize(
                \GuzzleHttp\json_encode($decoded['data']),
                $this->eventMapping[$decoded['event']],
                'json'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() == 'bitbucket_webhook';
    }
}
