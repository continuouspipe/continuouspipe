var Table = require('cli-table');

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

        var endStream = function() {
            getPod().then(function(pod) {
                var table = new Table({ 
                    head: ["Container", "Ready?", "Last state", "Reason"] 
                });

                if (pod.status && pod.status.containerStatuses) {
                    pod.status.containerStatuses.forEach(function(containerStatus) {
                        var lastState = containerStatus.lastState && containerStatus.lastState.terminated;

                        table.push([
                            containerStatus.name,
                            containerStatus.ready ? 'Yes' : 'No',
                            lastState ? 'terminated' : '',
                            lastState ? lastState.reason : ''
                        ])
                    });
                }

                logWritter.write("\n\n# Overview of the containers:\n"+table.toString());
            }).catch(function(error) {
                // Ignore any error so we always call the callback...
            }).then(function() {
                callback(hasData);
            });
        }
        
        stream.on('data', function(chunk) {
            hasData = true;

            logWritter.write(chunk.toString());
        });

        stream.on('close', function() {
            logWritter.write('[stream closed]');

            endStream();
        });

        stream.on('error', function(error) {
            logWritter.write('[stream error]');

            endStream();
        });

        stream.on('end', function(result) {
            logWritter.write('[stream ended]');

            endStream();
        });
    };

    var retryStream = function() {
        retryTimeout = setTimeout(function() {
            tryStream();
        }, 1000);
    };

    var getPod = function() {
        return new Promise(function(resolve, reject) {
            client.ns(target.namespace).po.get(target.pod, function(error, pod) {
                if (error) {
                    reject(error)
                } else {
                    resolve(pod)
                }
            });
        });
    }

    var tryStream = function() {
        return getPod().then(function(pod) {
            streamLog(pod, function(hasData) {
                if (!hasData) {
                    retryStream();
                }
            });
        }, function(error) {
            console.log('Unable to get pod', target.pod, ':', error, '. Retrying in 1 second.');

            retryStream();
        });
    };

    this.watch = function() {
        return tryStream();
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