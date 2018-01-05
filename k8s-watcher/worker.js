var bootstrap = require('./src/bootstrap'),
    processorFactory = require('./src/worker-processor');

bootstrap(function(queue, firebase) {
    queue.process('logs', 20, processorFactory(firebase));

    console.log('Processing up to 20 items of the "logs" queue.');
});
