var app = require('./app'),
    processorFactory = require('./worker/processor');

app(function(queue, firebase) {
    queue.process('logs', 25, processorFactory(firebase));

    console.log('Processing up to 25 items of the "logs" queue.');
});
