<?php

$for = $argv[1];

$timeout = isset($argv[2]) ? (int) $argv[2] : 120;
$startTime = time();
$phases = array("|", "/", "-", "\\");
$phase = 0;

while (time() < ($startTime + $timeout)) {
    if ($for == 'tunnel') {
        if ($address = get_tunnel_address()) {
            echo $address;
            exit(0);
        }
    } elseif ($for == 'api') {
        if (false !== @file_get_contents('http://localhost:81')) {
            exit(0);
        }
    } else {
        echo "Error: not sure what to wait for...";
        exit(1);
    }

    printf('%s%s', chr(8), $phases[($phase++) % 4]);
    sleep(1);
}

echo "Error: Timed-out waiting for the ngrok tunnel. Check the \"tunnel\" container.";
exit(1);


function get_tunnel_address()
{
    if (false === ($contents = @file_get_contents('http://localhost:4040/api/tunnels'))) {
        return null;
    }

    if (null === $json = @json_decode($contents, true)) {
        return null;
    }

    foreach ($json['tunnels'] as $tunnel) {
        if ($tunnel['proto'] == 'https') {
            return $tunnel['public_url'];
        }
    }

    return null;
}
