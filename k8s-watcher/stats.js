var app = require('./app'),
    processorFactory = require('./worker/processor');

app(function(queue, firebase, statsd) {
    setInterval(function() {
        get(queue)
        ('inactiveCount')
        ('completeCount')
        ('activeCount')
        ('failedCount')
        ('delayedCount')
        (function(error, stats) {
            console.log('Send metrics', stats);
            
            ['inactiveCount', 'completeCount', 'activeCount', 'failedCount', 'delayedCount'].forEach(function(metric) {
                statsd.gauge(metric, stats[metric]);
            });
        });
    }, 5000);
});

/**
 * Data fetching helper.
 */
function get( obj ) {
  var pending = 0
    , res     = {}
    , callback
    , done;

  return function _( arg ) {
    switch(typeof arg) {
      case 'function':
        callback = arg;
        break;
      case 'string':
        ++pending;
        obj[ arg ](function( err, val ) {
          if( done ) return;
          if( err ) return done = true, callback(err);
          res[ arg ] = val;
          --pending || callback(null, res);
        });
        break;
    }
    return _;
  };
}
