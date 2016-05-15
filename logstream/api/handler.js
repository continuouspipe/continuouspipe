var HttpHandlerFactory = function(LogsCollection) {
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

    var createLog = function(request, response) {
        getRequestJson(request, response, function(log) {
            LogsCollection.insert(log, function(error) {
                if (null !== error) {
                    response.writeHead(500);
                    response.end(JSON.stringify(error));
                } else {
                    response.writeHead(200);
                    response.end(JSON.stringify(log));
                }
            });
        });
    };

    var patchLog = function(request, response) {
        getRequestJson(request, response, function(patch) {
            return LogsCollection.update(request.logId, patch, function (error, log) {
                if (error !== null) {
                    response.writeHead(500);
                    response.end('Unable to update the log');

                    return;
                }

                response.writeHead(200);
                response.end(JSON.stringify(log));
            })
        });
    };

    return function(request, response) {
        var matches, routes = [
            {url: /^\/v1\/logs$/, method: 'POST', handler: createLog},
            {url: /^\/v1\/logs\/(.+)/, method: 'PATCH', handler: patchLog, parameterMapping: function(request, matches) {
                request.logId = matches[1];
            }}
        ];

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
