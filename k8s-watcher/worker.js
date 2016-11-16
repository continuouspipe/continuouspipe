var app = require('./app'),
    processorFactory = require('./worker/processor');

app(function(queue, firebase) {
    queue.process('logs', 20, processorFactory(firebase));

    console.log('Processing up to 20 items of the "logs" queue.');
});
