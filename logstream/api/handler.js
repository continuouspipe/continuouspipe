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

                callback(json);
            } catch (e) {
                response.writeHead(400);
                response.end('Invalid JSON');
            }
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
            return LogsCollection.update(request.logId, patch, function (error) {
                if (error !== null) {
                    response.writeHead(500);
                    response.end('Unable to update the log');

                    return;
                }

                LogsCollection.find(request.logId, function(error, log) {
                    if (error !== null) {
                        response.writeHead(500);
                        response.end('Unable to find updated log');
                    } else {
                        response.writeHead(200);
                        response.end(JSON.stringify(log));
                    }
                });
            })
        });
    };

    return function(request, response) {
        if (request.url == '/v1/logs' && request.method == 'POST') {
            return createLog(request, response);
        }

        var matches = request.url.match(/\/v1\/logs\/([^\/]+)/);
        if (matches !== null && request.method == 'PATCH') {
            request.logId = matches[1];

            return patchLog(request, response);
        }

        response.writeHead(404);
        response.end();
    };
};

module.exports = HttpHandlerFactory;
