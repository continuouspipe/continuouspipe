var WebSocket = require('ws'),
    url = require('url');

module.exports = function(server) {
    var webSocketServer = new WebSocket.Server({
        server: server
    });

    webSocketServer.on('connection', function(socket, req) {
        var location = url.parse(req.url, true),
            path = location.pathname,
            token = location.query.access_token,
            urlMatches = path.match(/^\/flows\/([a-z0-9-]+)\/cluster\/([^\/]+)\/([^\/]+)\/pod\/([a-z0-9-]+)$/i)

        if (null === urlMatches) {
            socket.send('Unexpected request')
            socket.close();
        } else if (!token) {
            socket.send('Bad request: missing authentication token');
            socket.close();
        } else {
            var matches = {
                flowUuid: urlMatches[1],
                cluster: urlMatches[2],
                namespace: urlMatches[3],
                pod: urlMatches[4],
            };

            var proxyHostname = process.env.KUBE_PROXY_HOSTNAME;
            var proxyScheme = process.env.KUBE_PROXY_SCHEME || 'wss';
            var proxyWebSocketUri =
                proxyScheme+'://x-token-auth:'+token+'@'+proxyHostname+
                '/'+matches.flowUuid+'/'+matches.cluster+
                '/api/v1'+
                '/namespaces/'+matches.namespace+
                '/pods/'+matches.pod+
                '/exec'+
                '?'+
                'stdout=1&stdin=1&stderr=1&tty=true'
            ;

            ['/bin/bash'].forEach(function(commandPart) {
                proxyWebSocketUri += '&command='+encodeURIComponent(commandPart);
            });

            var containerSocket = new WebSocket(proxyWebSocketUri, "base64.channel.k8s.io")
            containerSocket.on('message', function(data) {
                socket.send(
                    Buffer.from(data.slice(1), 'base64').toString("ascii")
                );
            });

            containerSocket.on('error', function(error) {
                console.log('error from container', error);
                socket.send('[error:'+error+']');
            });

            containerSocket.on('close', function() {
                console.log('Closed from remote');
                socket.send('[connection closed]');
                socket.close();
            });

            socket.on('message', function(message) {
                containerSocket.send(
                    '0' + Buffer.from(message, 'ascii').toString('base64')
                );
            });
        }
    });
};
