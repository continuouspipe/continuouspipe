

module.exports = function(firebase) {
    /**
     * What the logs of a given pod.
     *
     * 
     */
    return function(client, target, log, done) {
        var lines = target.limit !== undefined ? target.limit : 1000;
        var stream = client.ns(target.namespace).po.log({
            name: target.pod, 
            qs: {
                follow: true, 
                previous: target.previous || false,
                tailLines: lines
            }
        });
        
        stream.on('data', function(chunk) {
            log.push({
                type: 'text',
                contents: chunk.toString(),
            });
        });

        stream.on('close', function() {
            log.push({
                type: 'text',
                contents: '[stream closed]'
            });

            done();
        });

        stream.on('error', function(error) {
            log.push({
                type: 'text',
                contents: '[stream error]'
            });

            done();
        });

        stream.on('end', function(result) {
            log.push({
                type: 'text',
                contents: '[stream ended]'
            });

            done();
        });

        return function() {
            try {
                stream.destroy();
            } catch (e) {
                // Ignore if we can't destroy the stream.
            }
        };
    };
};
