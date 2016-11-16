var k8s = require('../k8s');

module.exports = function(firebase) {
    // Configuration
    var timeout = 1000 * 60 * 5;

    return function(job, done) {
        console.log('run job', job.id, job.zid, job.workerId);

        var data = job.data,
            log = firebase.child('logs').child(data.logId),
            client = k8s.createClientFromCluster(data.cluster);

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

        // Timeout the stream read
        var timeoutIdentifier = setTimeout(function(){
            console.log('[' + job.id + '] Destroying read stream after timeout');

            log.update({'timedOut': true});

            stream.destroy();
            finish();
        }, timeout);

        // Allow to finish the stream
        var finish = function() {
            log.update({'status': 'finished'});

            if (data.removeLog) {
                console.log('[' + job.id + '] Removing log "' + data.logId + '"');
                console.log('[' + job.id + '] Removing raw log "' + raw.key() + '"');

                log.remove();
                raw.remove();
            }

            console.log('[' + job.id + '] Processed');
            clearTimeout(timeoutIdentifier);

            done();
        };

        console.log('[' + job.id + '] Processing cluster ', data.cluster.address, ' namespace ', data.namespace, 'pod', data.pod);

        var lines = data.limit !== undefined ? data.limit : 1000;
        var stream = client.ns(data.namespace).po.log({
            name: data.pod, 
            qs: {
                follow: true, 
                previous: true,
                tailLines: lines
            }
        });
        
        stream.on('data', function(chunk) {
            rawChildren.push({
                type: 'text',
                contents: chunk.toString(),
            });
        });

        stream.on('close', function() {
            console.log('[' + job.id + '] Stream was closed');

            rawChildren.push({
                type: 'text',
                contents: '[stream closed]'
            });

            finish();
        });

        stream.on('error', function(error) {
            console.log('[' + job.id + '] Stream was errored', error);

            rawChildren.push({
                type: 'text',
                contents: '[stream error]'
            });

            finish();
        });

        stream.on('end', function(result) {
            console.log('[' + job.id + '] Stream was ended', result);

            rawChildren.push({
                type: 'text',
                contents: '[stream ended]'
            });

            finish();
        });
    };
};
