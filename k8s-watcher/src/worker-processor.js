var k8s = require('./kubernetes'),
    LogsWatcher = require('./watcher/logs-watcher'),
    LogWritter = require('./watcher/log-writter');

module.exports = function(firebase) {
    // Configuration
    var timeout = 1000 * 60 * 5,
        eventsWatcher = require('./watcher/events')();

    return function(job, done) {
        console.log('[' + job.id + '] [DEBUG]', job.zid, job.workerId);

        var firebaseDatabase = firebase.database(),
            data = job.data,
            log = firebaseDatabase.ref(data.logId);

        k8s.createClientFromCluster(data.cluster).then(function(client) {
            console.log('[' + job.id + '] Processing cluster ', data.cluster.address, ' namespace ', data.namespace, 'pod', data.pod);

            // Create the raw log
            var raw = firebaseDatabase.ref('raws').push({
                type: 'raw',
                logId: data.logId,
            });

            var rawChildren = raw.child('children');

            // Add the raw reference
            log.child('children').push({
                type: 'raw',
                path: {
                    identifier: '/raws/' + raw.key,
                    database: {
                        name: process.env.FIREBASE_DATABASE_NAME
                    }
                }
            });

            // Add the events reference
            var eventsLog = log.child('events');

            // Watch logs
            var logsWatcher = new LogsWatcher(client, data, new LogWritter(rawChildren));
            logsWatcher.watch();

            // Watch events
            var cancelEvents = eventsWatcher(client, data, eventsLog);

            // Timeout the stream read
            var timeoutIdentifier = setTimeout(function(){
                console.log('[' + job.id + '] Destroying read stream after timeout');

                log.update({'timedOut': true});

                finish();
            }, timeout);

            // Allow to finish the stream
            var finish = function() {
                cancelEvents();
                logsWatcher.stop();

                // Wait 1 second to allow the content to flush
                setTimeout(function() {
                    log.update({'status': 'finished'});                
                }, 1000);

                if (data.removeLog) {
                    console.log('[' + job.id + '] Scheduling removal of the log in 5 seconds');
                    setTimeout(function() {
                        console.log('[' + job.id + '] Removing log "' + data.logId + '"');
                        console.log('[' + job.id + '] Removing raw log "' + raw.key + '"');

                        log.remove();
                        raw.remove();
                    }, 5000);
                }

                console.log('[' + job.id + '] Processed');
                clearTimeout(timeoutIdentifier);

                done();
            };
        });        
    };
};
