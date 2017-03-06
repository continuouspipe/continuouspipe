angular.module('continuousPipeRiver')
    .directive('logDuration', function() {
        return {
            link: function(scope, element, attributes) {
                scope.$watchGroup(['status', 'runningAt', 'successAt', 'failureAt'].map(function(attribute) {
                    return attributes.logDuration+'.'+attribute;
                }), function() {
                    refreshDuration(scope[attributes.logDuration]);
                });

                var interval = null,
                    displayDifference = function(beginning, end) {
                        var milliseconds = end - beginning;
                        var minutes = Math.floor(milliseconds / 1000 / 60);
                        milliseconds -= minutes * 1000 * 60;
                        var seconds = Math.floor(milliseconds / 1000);

                        element.text(('0'+minutes).slice(-2)+':'+('0'+seconds).slice(-2));
                    },
                    refreshDuration = function(log) {
                    // Clear interval if it exists
                    interval && clearInterval(interval);

                    if (!log.runningAt) {
                        return element.text('');
                    }

                    function parseDate(s) {
                        return +new Date(s.split('+')[0]);
                    }

                    var finishAt = log.successAt || log.failureAt;
                    if (!finishAt) {
                        interval = setInterval(function() {
                            displayDifference(parseDate(log.runningAt), new Date());
                        }, 1000);
                    } else {
                        displayDifference(parseDate(log.runningAt), parseDate(finishAt));
                    }
                };

                scope.$on('$destroy', function() {
                    interval && clearInterval(interval);
                });
            }
        }
    });
