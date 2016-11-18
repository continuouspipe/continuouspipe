
module.exports = function(firebase) {
    /**
     * What the events of a given pod.
     *
     * 
     */
    return function(client, target, log) {
        var eventsLoop = setInterval(function() {
            client.ns(target.namespace).events.get({
                qs: {
                    fieldSelector: [
                        'involvedObject.name='+target.pod,
                        'involvedObject.namespace='+target.namespace
                    ].join(',')
                }
            }, function(error, list) {
                if (error) {
                    return console.log('Unable to load events', error);
                }

                log.set(list.items);
            });
        }, 1000);

        return function() {
            console.log('Stopping events streaming');

            clearInterval(eventsLoop);
        };
    };
};
