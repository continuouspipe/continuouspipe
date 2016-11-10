var http = require('http'),
    raven = require('raven'),
    process = require('process'),
    handler = require('./handler')();

// Configure the Sentry exception collection
var sentry = new raven.Client(process.env.SENTRY_DSN);
sentry.patchGlobal();

// Start the HTTP server
var port = process.env.PORT || 80;
http.createServer(handler).listen(port);
console.log('Started HTTP server at port '+port);

// Display the process exceptions
process.on('uncaughtException', function (err) {
    console.error((new Date).toUTCString() + ' uncaughtException:', err.message);
    console.error(err.stack);

    process.exit(1);
});
