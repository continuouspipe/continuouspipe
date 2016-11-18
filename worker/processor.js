var k8s = require('../k8s');

module.exports = function(firebase) {
    // Configuration
    var timeout = 1000 * 60 * 5,
        logsWatcher = require('../watcher/logs')(firebase),
        eventsWatcher = require('../watcher/events')(firebase);

    return function(job, done) {
        console.log('[' + job.id + '] [DEBUG]', job.zid, job.workerId);

        var data = job.data,
            log = firebase.child('logs').child(data.logId),
            client = k8s.createClientFromCluster(data.cluster);

        console.log('[' + job.id + '] Processing cluster ', data.cluster.address, ' namespace ', data.namespace, 'pod', data.pod);

        // Create the raw log
        var raw = firebase.child('raws').push({
            type: 'raw',
            logId: data.logId,
        });

        var rawChildren = raw.child('children');

        // Add the raw reference
        log.child('children').push({
            type: 'raw',
            path: '/raws/' + raw.key()
        });

        // Add the events reference
        var eventsLog = log.child('events');

        // Watch logs
        var cancelLogs = logsWatcher(client, data, rawChildren, function() {
            finish();
        });

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
            cancelLogs();

            // Wait 1 second to allow the content to flush
            setTimeout(function() {
                log.update({'status': 'finished'});                
            }, 1000);

            if (data.removeLog) {
                console.log('[' + job.id + '] Scheduling removal of the log in 5 seconds');
                setTimeout(function() {
                    console.log('[' + job.id + '] Removing log "' + data.logId + '"');
                    console.log('[' + job.id + '] Removing raw log "' + raw.key() + '"');

                    log.remove();
                    raw.remove();
                }, 5000);
            }

            console.log('[' + job.id + '] Processed');
            clearTimeout(timeoutIdentifier);

            done();
        };


        
    };
};
