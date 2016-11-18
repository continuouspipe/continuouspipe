var ajv = require('ajv')(),
    k8s = require('../k8s')

var HttpHandlerFactory = function(queue, firebase, statsd) {
    var redirect = function(request, response) {
        response.writeHead(200, {"Content-Type": "text/html"});
        response.write('<html><head><meta http-equiv="refresh" content="0; url=https://continuouspipe.io" /></head></html>');
        response.end();
    };

    var getRequestBody = function(request, callback) {
        var fullBody = '';

        request.on('data', function(chunk) {
            fullBody += chunk.toString();
        });

        request.on('end', function() {
            callback(fullBody);
        });
    };

    var getRequestJson = function(request, response, callback) {
        getRequestBody(request, function(body) {
            try {
                var json = JSON.parse(body);
            } catch (e) {
                response.writeHead(400);
                response.end('Invalid JSON');

                return;
            }

            callback(json);
        });
    };

    var watchLogs = function(request, response) {
        var schema = {
            "properties": {
                "cluster": {
                      "type": "object",
                      "properties": {
                        "address": {
                              "type": "string"
                        },
                        "version": {
                              "type": "string"
                        },
                        "username": {
                              "type": "string"
                        },
                        "password": {
                              "type": "string"
                        }
                      },
                      "required": [
                        "address",
                        "version",
                        "username",
                        "password"
                      ]
                },
                "namespace": {
                      "type": "string"
                },
                "pod": {
                      "type": "string"
                }
              },
              "required": [
                "cluster",
                "namespace",
                "pod"
              ]
        };

        getRequestJson(request, response, function(data) {
            if (!ajv.validate(schema, data)) {
                response.setHeader('Content-Type', 'application/json');
                response.writeHead(400);
                response.end(JSON.stringify(ajv.errors));

                return;
            }

            var client = k8s.createClientFromCluster(data.cluster);
            client.ns(data.namespace).po.get(data.pod, function(error, pod) {
                if (error) {
                    response.writeHead(400);
                    response.end(JSON.stringify({
                        code: error.code, 
                        message: error.toString()
                    }));

                    return;
                }

                // If the `logId` is not given, create one
                if (!data.logId) {
                    data.logId = firebase.child('logs').push({'type': 'container'}).key();
                    console.log('Created log "' + data.logId + '"');
                    data.removeLog = true;
                }

                if (undefined === data.removeLog) {
                    data.removeLog = false;
                }

                if (pod.status && pod.status.phase != 'Running') {
                    console.log('Pod', data.pod, 'is not running, will load previous logs');
                    data.previous = true;
                }

                var job = queue.create('logs', data).save(function(error) {
                    response.setHeader('Content-Type', 'application/json');

                    if (error) {
                        response.writeHead(500);
                        response.end(JSON.stringify(error));
                    } else {
                        response.writeHead(200);
                        response.end(JSON.stringify({
                            'logId': data.logId,
                            'jobId': job.id
                        }));
                    }
                }).removeOnComplete(true).save();
            });

            statsd.increment('api.http.watch_logs');
        });
    };

    return function(request, response) {
        var matches,
            matchFirstArgumentAsLogId = function(request, matches) {
                request.logId = matches[1];
            },
            routes = [
                {url: /^\/$/, method: 'GET', handler: redirect},
                {url: /^\/v1\/watch\/logs$/, method: 'POST', handler: watchLogs},
            ]
        ;

        for (var i = 0; i < routes.length; i++) {
            var route = routes[i];

            if (request.method == route.method && null !== (matches = request.url.match(route.url))) {
                route.parameterMapping && route.parameterMapping(request, matches);

                return route.handler(request, response);
            }
        }

        response.writeHead(404);
        response.end();
    };
};

module.exports = HttpHandlerFactory;
