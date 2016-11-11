angular.module('continuousPipeRiver')
    .directive('logs', ['RecursionHelper', '$http', function(RecursionHelper, $http) {
        return {
            restrict: 'E',
            scope: {
                parent: '=',
                level: '@',
                scope: '@'
            },
            templateUrl: 'logs/views/logs.ng.html',
            controller: ['$scope', function ($scope) {
                $scope.follow = false;
                $scope.displayChildrenOf = [];
                $scope.shouldDisplayChildrenOf = function(logId) {
                    return $scope.level == 1 || $scope.displayChildrenOf[logId];
                };
                $scope.$watch('level', function(value) {
                    $scope.level = parseInt(value);
                });

                $scope.toggleChildrenDisplay = function(logId) {
                    $scope.displayChildrenOf[logId] = !$scope.displayChildrenOf[logId];
                };

                var loadArchive = function () {
                    if (!$scope.parent || !$scope.parent.archived) {
                        return;
                    }

                    $http.get($scope.parent.archive, {
                        skipAuthorization: true
                    }).then(function (response) {
                        $scope.parent = response.data;
                    }, function (error) {
                        $scope.parent.children = [
                            {type: 'text', status: 'error', contents: 'Unable to log the children from archive'}
                        ];
                    });
                };

                $scope.$watch('parent.archived', function(archived) {
                    if (archived) {
                        loadArchive();
                    }
                });

                if ($scope.parent.$loaded) {
                    $scope.parent.$loaded().then(loadArchive);
                } else {
                    loadArchive();
                }
            }],
            compile: function(element) {
                return RecursionHelper.compile(element);
            }
        }
    }])
    .directive('rawLogsContent', function($sce) {
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

                function encode(r) {
                    return r.replace(/[\x26\x0A\<>'"]/g, function(r) {
                        return"&#"+r.charCodeAt(0)+";";
                    });
                }

                scope.$watch('rawLogsContent', function(log) {
                    var value = concatLogChildren(log),
                        sanitizedValue = encode(value),
                        html = ansi_up.ansi_to_html(sanitizedValue);

                    $(element).html(html).trigger('updated-html');
                });
            }
        };
    })
    .directive('followScroll', function() {
        return {
            link: function(scope, element, attributes) {
                var doScrollToBottom = function(element) {
                    element.scrollTop(element[0].scrollHeight);
                };
                var scrollToTheBottom = function() {
                    var dialog = $('md-dialog-content');
                    if (dialog.length) {
                        doScrollToBottom(dialog);
                    }

                    doScrollToBottom(element);
                };

                scope.$watch(attributes.followScroll, function(follow) {
                    element[follow ? 'on' : 'off']('updated-html', scrollToTheBottom);

                    if (follow) {
                        scrollToTheBottom();
                    }
                });
            },
            restrict: 'A'
        };
    })
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

                    var finishAt = log.successAt || log.failureAt;
                    if (!finishAt) {
                        interval = setInterval(function() {
                            displayDifference(Date.parse(log.runningAt), new Date());
                        }, 1000);
                    } else {
                        displayDifference(Date.parse(log.runningAt), Date.parse(finishAt));
                    }
                };
            }
        }
    })
;
