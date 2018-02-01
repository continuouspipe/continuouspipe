var http = require('http'),
    handlerFactory = require('./src/http-handler'),
    webSocketServerFactory = require('./src/websocket'),
    processorFactory = require('./src/worker-processor'),
    bootstrap = require('./src/bootstrap');

bootstrap(function(queue, firebase, statsd) {
    if (process.env.START_HTTP === 'true') {
        // Start the HTTP server
        var handler = handlerFactory(queue, firebase, statsd);
        var port = process.env.PORT || 80,
            server = http.createServer(handler);

        // Add the WebSocket server
        webSocketServerFactory(server);

        server.listen(port);
        console.log('Started HTTP server at port ' + port);
    }

    // Start the worker
    if (process.env.START_WORKER === 'true') {
        queue.process('logs', 20, processorFactory(firebase));
        console.log('Processing up to 20 items of the "logs" queue.');
    }
});
