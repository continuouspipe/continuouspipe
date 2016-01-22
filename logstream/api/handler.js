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

    var createLog = function(request, response) {
        getRequestBody(request, function(body) {
            try {
                var log = JSON.parse(body);
            } catch (e) {
                response.writeHead(400);
                response.end('Invalid JSON');

                return;
            }

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

    return function(request, response) {
        console.log(request.url, request.method);
        if (request.url == '/v1/logs' && request.method == 'POST') {
            return createLog(request, response);
        }

        var matches = request.url.match(/\/v1\/logs\/([^\/]+)/);
        if (matches !== null) {
            return LogsCollection.find(matches[1], function(error, log) {
                if (error !== null) {
                    response.writeHead(404);
                    response.end('Not found');
                } else {
                    response.writeHead(200);
                    response.end(JSON.stringify(log));
                }
            });
        }

        response.writeHead(404);
        response.end();
    };
};

module.exports = HttpHandlerFactory;
