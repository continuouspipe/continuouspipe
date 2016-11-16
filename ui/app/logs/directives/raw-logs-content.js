angular.module('continuousPipeRiver')
    .directive('rawLogsContent', function($sce, $firebaseArray, LogFinder) {
        return {
            restrict: 'A',
            scope: {
                rawLogsContent: '='
            },
            link: function(scope, element) {
                var concatLogChildren = function(log) {
                    var value = '';

                    if (!log.children || log.children.length <= 0) {
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

                function encode(r) {
                    return r.replace(/[\x26\x0A\<>'"]/g, function(r) {
                        return"&#"+r.charCodeAt(0)+";";
                    });
                }

                scope.$watch('rawLogsContent', function(log) {
                    if (log.path) {
                        if (logsArray === null) {
                            logsArray = $firebaseArray(LogFinder.getReference(log.path+'/children'));
                            logsArray.$watch(function(event) {
                                if (event.event == 'child_added') {
                                    var record = logsArray.$getRecord(event.key),
                                        sanitizedValue = encode(record.contents),
                                        html = ansi_up.ansi_to_html(sanitizedValue);

                                    $(element).append(html).trigger('updated-html');
                                }
                            });
                        }
                    } else {
                        var value = concatLogChildren(log),
                            sanitizedValue = encode(value),
                            html = ansi_up.ansi_to_html(sanitizedValue);

                        $(element).html(html).trigger('updated-html');
                    }
                });
            }
        };
    });
