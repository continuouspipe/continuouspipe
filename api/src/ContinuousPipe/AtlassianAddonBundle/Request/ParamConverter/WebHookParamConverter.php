<?php

namespace ContinuousPipe\AtlassianAddonBundle\Request\ParamConverter;

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
