<?php

namespace ContinuousPipe\River\Pipe\PublicEndpoint;

use ContinuousPipe\Pipe\Client\PublicEndpoint;
use ContinuousPipe\Pipe\Client\PublicEndpointPort;

class PublicEndpointWriter
{
    /**
     * @param PublicEndpoint $endpoint
     *
     * @return string
     */
    public function writeAddress(PublicEndpoint $endpoint) : string
    {
        if (0 === count($endpoint->getPorts())) {
            return $endpoint->getAddress();
        }

        $addresses = array_map(function (PublicEndpointPort $port) use ($endpoint) {
            return $this->writeAddressWithPort($endpoint, $port);
        }, $endpoint->getPorts());

        $address = array_shift($addresses);

        if (count($addresses) > 0) {
            $address .= ' ('.implode(', ', $addresses).')';
        }

        return $address;
    }

    /**
     * @param PublicEndpoint     $endpoint
     * @param PublicEndpointPort $port
     *
     * @return string
     */
    private function writeAddressWithPort(PublicEndpoint $endpoint, PublicEndpointPort $port)
    {
        if (80 == $port->getNumber()) {
            return 'http://'.$endpoint->getAddress();
        } elseif (443 == $port->getNumber()) {
            return 'https://'.$endpoint->getAddress();
        } elseif (23 == $port->getNumber()) {
            return 'ftp://'.$endpoint->getAddress();
        }

        $address = $endpoint->getAddress().':'.$port->getNumber();
        if ($port->getProtocol() != PublicEndpointPort::PROTOCOL_TCP) {
            $address .= ' ('.$port->getProtocol().')';
        }

        return $address;
    }
}
