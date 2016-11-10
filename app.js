var raven = require('raven'),
    process = require('process'),
    kue = require('kue'),
    Firebase = require('firebase');

module.exports = function(callback) {
	// Display the process exceptions
	process.on('uncaughtException', function (err) {
	    console.error((new Date).toUTCString() + ' uncaughtException:', err.message);
	    console.error(err.stack);

	    process.exit(1);
	});

	// Configure the Sentry exception collection
	var sentry = new raven.Client(process.env.SENTRY_DSN);
	sentry.patchGlobal();

	// Create the queue
	var queue = kue.createQueue({
	    prefix: 'q',
	    redis: {
		    host: process.env.REDIS_HOST || 'redis'
	    }
	});

	// Create the firebase connection
	var firebase_application = process.env.FIREBASE_APP;
	if (!firebase_application) {
	    console.log('No configured firebase application');
	    process.exit(1);
	}

	var firebase = new Firebase('https://'+firebase_application+'.firebaseio.com/');

	callback(queue, firebase);
};
