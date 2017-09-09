angular.module('continuousPipeRiver')
    .factory('HtmlWriter', function() {
        function encode(r) {
            return r.replace(/[\x26\x0A\<>'"]/g, function(r) {
                return"&#"+r.charCodeAt(0)+";";
            });
        }

        function rawToHtml(raw) {
            return ansi_up.ansi_to_html(encode(raw));
        }

        var throttlePeriod = 100, // In milliseconds
            throttle = function(func, limit) {
            var inThrottle,
                lastFunc,
                lastRan;

            return function() {
                var context = this,
                    args = arguments;
                if (!inThrottle) {
                    func.apply(context, args);
                    lastRan = Date.now();
                    inThrottle = true;
                } else {
                    clearTimeout(lastFunc);
                    lastFunc = setTimeout(function() {
                        if ((Date.now() - lastRan) >= limit) {
                            func.apply(context, args);
                            lastRan = Date.now()
                        }
                    }, limit - (Date.now() - lastRan))
                }
            };
        };

        return function(element) {
            var appendBuffer = '';

            this.appendRaw = function(raw) {
                appendBuffer += raw;

                this.doAppend();
            };

            this.doAppend = throttle(function() {
                var toAppend = appendBuffer;

                if (toAppend != '') {
                    appendBuffer = '';

                    $(element).append(rawToHtml(toAppend)).trigger('updated-html');
                }
            }, 100);

            this.updateRaw = throttle(function(raw) {
                $(element).html(rawToHtml(raw)).trigger('updated-html');
            }, throttlePeriod);
        };
    })
    .directive('rawLogsContent', function($sce, $firebaseArray, LogFinder, HtmlWriter) {
        return {
            restrict: 'A',
            scope: {
                rawLogsContent: '='
            },
            link: function(scope, element) {
                var concatLogChildren = function(log) {
                    var value = '';

                    if (!log.children || log.children.length <= 0) {
                        if (log.contents) {
                            value = log.contents;
                        }
                        
                        return value;
                    }

                    for (var key in log.children) {
                        if (!log.children.hasOwnProperty(key)) {
                            continue;
                        }

                        value += log.children[key].contents;
                    }

                    return value;
                };

                var logsArray = null;

                scope.$watch('rawLogsContent', function(log) {
                    var htmlWriter = new HtmlWriter(element);

                    if (log.path) {
                        if (logsArray === null) {
                            var path = log.path;
                            if (typeof path == 'string') {
                                path = {
                                    identifier: path
                                };
                            }

                            // Get path's children reference
                            path.identifier = path.identifier+'/children';

                            LogFinder.getReference(path).then(function(reference) {
                                logsArray = $firebaseArray(reference);
                                logsArray.$watch(function(event) {
                                    if (event.event == 'child_added') {
                                        var record = logsArray.$getRecord(event.key);

                                        htmlWriter.appendRaw(record.contents);
                                    }
                                });
                            });
                        }
                    } else {
                        var value = concatLogChildren(log);

                        htmlWriter.updateRaw(value);
                    }
                });
            }
        };
    });
