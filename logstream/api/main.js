var http2 = require('http2'),
    fs = require('fs'),
    LogsCollection = require('./collections/logs.js'),
    HandlerFactory = require('./handler'),
    Firebase = require('firebase');

// Create the firebase connection
var firebase_application = process.env.FIREBASE_APP;
if (!firebase_application) {
    console.log('No configured firebase application');
    process.exit(1);
}

var firebase = new Firebase('https://'+firebase_application+'.firebaseio.com/');

// Start the HTTP server
var port = process.env.PORT || 443;
console.log('Start HTTP server at port '+port);
var options = {
    key: fs.readFileSync('keys/server.key'),
    cert: fs.readFileSync('keys/server.crt')
};

var handler = HandlerFactory(
    new LogsCollection(
        firebase.child('logs')
    )
);

http2.createServer(options, handler).listen(port);
