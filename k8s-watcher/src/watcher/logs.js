module.exports = function(firebase) {
    /**
     * What the logs of a given pod.
     *
     * 
     */
    return function(client, target, log, done) {
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

                log.push({
                    type: 'text',
                    contents: chunk.toString(),
                });
            });

            stream.on('close', function() {
                if (hasData) {
                    log.push({
                        type: 'text',
                        contents: '[stream closed]'
                    });
                }

                callback(hasData);
            });

            stream.on('error', function(error) {
                log.push({
                    type: 'text',
                    contents: '[stream error]'
                });

                callback(hasData);
            });

            stream.on('end', function(result) {
                if (hasData) {
                    log.push({
                        type: 'text',
                        contents: '[stream ended]'
                    });
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

        tryStream();

        return function() {
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
        };
    };
};
