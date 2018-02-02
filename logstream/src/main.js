var http2 = require('http2'),
    http = require('http'),
    fs = require('fs'),
    LogsCollection = require('./collections/logs.js'),
    HandlerFactory = require('./handler'),
    Firebase = require('firebase-admin'),
    Raven = require('raven');

// Create the firebase connection
var firebase_application = process.env.FIREBASE_APP;
if (!firebase_application) {
    console.log('No configured firebase application');
    process.exit(1);
}

Firebase.initializeApp({
    credential: Firebase.credential.cert(require(process.env.FIREBASE_SERVICE_ACCOUNT_PATH)),
    databaseURL: 'https://'+firebase_application+'.firebaseio.com',
    storageBucket: firebase_application+'.appspot.com'
});

// Configure the Sentry exception collection
Raven.config(process.env.SENTRY_DSN).install();

// Start the HTTP server
var port = process.env.PORT || 443;
console.log('Start HTTP server at port '+port);
var options = {
    key: fs.readFileSync(process.env.SSL_CERTIFICATE_PRIVATE_KEY_PATH),
    cert: fs.readFileSync(process.env.SSL_CERTIFICATE_CERTIFICATE_PATH)
};

var handler = HandlerFactory(
    new LogsCollection(
        Firebase.database().ref('logs'),
        Firebase.storage().bucket()
    )
);

http2.createServer(options, handler).listen(port);
http.createServer(handler).listen(process.env.HTTP_PORT || 80);
