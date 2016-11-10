var ajv = require('ajv')();


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

			console.log('watch', data);


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
