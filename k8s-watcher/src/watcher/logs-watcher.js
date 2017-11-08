module.exports = function(client, target, logWritter) {
    var stream = null,
        retryTimeout = null;

    var streamLog = function(pod, callback) {
        var previous = false;

        if (pod.status && pod.status.phase != 'Running') {
            console.log('Pod', pod.metadata.name, 'is not running, will load previous logs');
            previous = true;
        }

        var lines = target.limit !== undefined ? target.limit : 1000,
            hasData = false;
        
        stream = client.ns(target.namespace).po.log({
            name: pod.metadata.name, 
            qs: {
                follow: true, 
                previous: previous,
                tailLines: lines
            }
        });
        
        stream.on('data', function(chunk) {
            hasData = true;

            logWritter.write(chunk.toString());
        });

        stream.on('close', function() {
            if (hasData) {
                logWritter.write('[stream closed]');
            }

            callback(hasData);
        });

        stream.on('error', function(error) {
            logWritter.write('[stream error]');

            callback(hasData);
        });

        stream.on('end', function(result) {
            if (hasData) {
                logWritter.write('[stream ended]');
            }

            callback(hasData);
        });
    };

    var retryStream = function() {
        retryTimeout = setTimeout(function() {
            tryStream();
        }, 1000);
    };

    var tryStream = function() {
        client.ns(target.namespace).po.get(target.pod, function(error, pod) {
            if (error) {
                console.log('Unable to get pod', target.pod, ':', error, '. Retrying in 1 second.');

                retryStream();
                
                return;
            }

            streamLog(pod, function(hasData) {
                if (!hasData) {
                    retryStream();
                }
            });
        });
    };

    this.watch = function() {
        tryStream();
    }

    this.stop = function() {
        try {
            if (stream) {
                stream.destroy();
            }
        } catch (e) {
            // Ignore if we can't destroy the stream.
        }

        if (retryTimeout) {
            clearTimeout(retryTimeout);
        }
    }
};