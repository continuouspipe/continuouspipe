angular.module('continuousPipeRiver')
    .directive('rawLogsContent', function($sce, $firebaseArray, LogFinder) {
        return {
            restrict: 'A',
            scope: {
                rawLogsContent: '='
            },
            link: function(scope, element) {
                console.log('link raw logs content');

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

                function encode(r) {
                    return r.replace(/[\x26\x0A\<>'"]/g, function(r) {
                        return"&#"+r.charCodeAt(0)+";";
                    });
                }

                function rawToHtml(raw) {
                    return ansi_up.ansi_to_html(encode(raw));
                }

                scope.$watch('rawLogsContent', function(log) {
                    console.log('watched', log);
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

                                        $(element).append(rawToHtml(record.contents)).trigger('updated-html');
                                    }
                                });
                            });
                        } else {
                            console.log('not doing anything...');
                        }
                    } else {
                        var value = concatLogChildren(log);

                        $(element).html(rawToHtml(value)).trigger('updated-html');
                    }
                });
            }
        };
    });
