var Raven = require('raven');

var HttpHandlerFactory = function(LogsCollection) {
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
            });
        });
    };

    var archiveLog = function(request, response) {
        return LogsCollection.archive(request.logId, function (error, log) {
            if (error !== null) {
                response.writeHead(500);
                response.end('Unable to archive the log');

                return;
            }

            response.writeHead(200);
            response.end(JSON.stringify(log));
        });
    };

    var getLog = function(request, response) {
        return LogsCollection.fetch(request.logId, function (error, log) {
            if (error !== null) {
                response.writeHead(500);
                response.end('Unable to get the log');

                return;
            }

            response.writeHead(200);
            response.end(JSON.stringify(log));
        });
    };

    return function(request, response) {
        var matches,
            matchFirstArgumentAsLogId = function(request, matches) {
                request.logId = matches[1];
            },
            routes = [
            {url: /^\/$/, method: 'GET', handler: redirect},
            {url: /^\/v1\/logs$/, method: 'POST', handler: createLog},
            {url: /^\/v1\/logs\/(.+)/, method: 'PATCH', handler: patchLog, parameterMapping: matchFirstArgumentAsLogId},
            {url: /^\/v1\/logs\/(.+)/, method: 'GET', handler: getLog, parameterMapping: matchFirstArgumentAsLogId},
            {url: /^\/v1\/archive\/(.+)/, method: 'POST', handler: archiveLog, parameterMapping: matchFirstArgumentAsLogId}
        ];

        for (var i = 0; i < routes.length; i++) {
            var route = routes[i];

            if (request.method == route.method && null !== (matches = request.url.match(route.url))) {
                route.parameterMapping && route.parameterMapping(request, matches);

                try {
                    return route.handler(request, response);
                } catch (e) {
                    console.log(e);

                    Raven.captureException(e);

                    response.writeHead(500);
                    response.end();
                }
            }
        }

        response.writeHead(404);
        response.end();
    };
};

module.exports = HttpHandlerFactory;
