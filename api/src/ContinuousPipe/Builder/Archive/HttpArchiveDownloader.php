<?php

namespace ContinuousPipe\Builder\Archive;

use ContinuousPipe\Builder\Request\ArchiveSource;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

class HttpArchiveDownloader implements ArchiveDownloader
{
    private $httpClient;

    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * {@inheritdoc}
     */
    public function download(ArchiveSource $archive, string $to)
    {
        try {
            $this->httpClient->request('get', $archive->getUrl(), [
                'save_to' => $to,
                'headers' => $archive->getHeaders(),
            ]);
        } catch (RequestException $e) {
            if (null !== ($response = $e->getResponse())) {
                try {
                    $contents = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

                    if (isset($contents['error']['message'])) {
                        $message = $contents['error']['message'];
                    }
                    if (isset($contents['error']['code'])) {
                        $code = $contents['error']['code'];
                    }
                } catch (\InvalidArgumentException $errorException) {
                    // Handle the exception as if it wasn't supported
                }
            }

            if (!isset($message)) {
                $message = $e->getMessage();
            }
            if (!isset($code)) {
                $code = $e->getCode();
            }

            throw new ArchiveException($message, $code, $e);
        }
    }
}
