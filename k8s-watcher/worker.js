var app = require('./app');

app(function(queue, firebase) {
	queue.process('logs', 25, function(job, done){
		var data = job.data,
			log = firebase.child('logs').child(data.logId);

		console.log('[' + job.id + '] proceed', data);

		if (data.removeLog) {
			console.log('[' + job.id + '] Removing log "' + data.logId + '"');
			
			log.remove();
		}

		done();
	});

	console.log('Processing up to 25 items of the "logs" queue.');
});
