var http2 = require('http2'),
    fs = require('fs'),
    MongoClient = require('mongodb').MongoClient,
    LogsCollection = require('./collections/logs.js'),
    HandlerFactory = require('./handler');

// MongoDB connection
var mongoUrl = process.env.MONGO_URL;
console.log('Connecting to MongoDB at '+mongoUrl);
MongoClient.connect(mongoUrl, function(error, db) {
    if (error !== null) {
        console.log(error);
        process.exit(1);
    }

    console.log("Connected correctly to MongoDB.");

    // Start the HTTP server
    var port = process.env.PORT || 443;
    console.log('Start HTTP server at port '+port);
    var options = {
        key: fs.readFileSync('keys/server.key'),
        cert: fs.readFileSync('keys/server.crt')
    };

    var handler = HandlerFactory(
        new LogsCollection(db)
    );

    http2.createServer(options, handler).listen(port);
});
