<?php

namespace ContinuousPipe\Google\Http;

use ContinuousPipe\Google\GoogleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

final class GoogleHttpUtils
{
    /**
     * @param ResponseInterface $response
     *
     * @return string
     */
    private static function getMessageFromResponse(ResponseInterface $response)
    {
        $contents = $response->getBody()->getContents();

        try {
            $json = \GuzzleHttp\json_decode($contents, true);

            if (isset($json['error']['message'])) {
                return $json['error']['message'];
            }
        } catch (\InvalidArgumentException $e) {
        }

        return 'Unexpected response ('.$response->getStatusCode().')';
    }

    /**
     * @param RequestException $e
     *
     * @return GoogleException
     */
    public static function createGoogleExceptionFromRequestException($e)
    {
        if (null !== ($response = $e->getResponse())) {
            $message = self::getMessageFromResponse($response);
        } else {
            $message = $e->getMessage();
        }

        return new GoogleException($message, $e->getCode(), $e);
    }
}
