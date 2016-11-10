var http = require('http'),
    handlerFactory = require('./api/handler'),
    app = require('./app');

app(function(queue, firebase) {
	var handler = handlerFactory(queue, firebase);

	// Start the HTTP server
	var port = process.env.PORT || 80;
	http.createServer(handler).listen(port);
	console.log('Started HTTP server at port '+port);
});
