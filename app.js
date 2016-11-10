var raven = require('raven'),
    process = require('process'),
    kue = require('kue'),
    Firebase = require('firebase'),
	StatsD = require('node-statsd');

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

	// Gracefully quit
	process.once('SIGTERM', function () {
	    queue.shutdown(5000, function(err) {
		    console.log('Queue shutdown: ', err);

		    process.exit(0);
	    });
	});


	// Create the firebase connection
	var firebase_application = process.env.FIREBASE_APP;
	if (!firebase_application) {
	    console.log('[WARNING] No configured firebase application');
	    var firebase = null;
	} else {
		var firebase = new Firebase('https://'+firebase_application+'.firebaseio.com/');
	}

	// Create the Statsd connection
	var statsd = new StatsD({
		host: process.env.STATSD_HOST,
		port: process.env.STATSD_PORT,
		prefix: process.env.STATSD_PREFIX,
	});


	callback(queue, firebase, statsd);
};
