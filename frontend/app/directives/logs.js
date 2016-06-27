angular.module('logstream')
    .directive('logs', ['RecursionHelper', '$http', function(RecursionHelper, $http) {
        return {
            restrict: 'E',
            scope: {
                parent: '='
            },
            templateUrl: 'views/logs/logs.ng.html',
            controller: ['$scope', function ($scope) {
                $scope.follow = false;
                $scope.displayChildrenOf = [];
                $scope.toggleChildrenDisplay = function(logId) {
                    $scope.displayChildrenOf[logId] = !$scope.displayChildrenOf[logId];
                };

                var loadArchive = function (parent) {
                    if (!parent || !parent.archived) {
                        return;
                    }

                    $http.get($scope.parent.archive).then(function (response) {
                        $scope.parent = response.data;
                    }, function (error) {
                        $scope.parent.children = [
                            {type: 'text', status: 'error', contents: 'Unable to log the children from archive'}
                        ];
                    });
                };

                if ($scope.parent.$loaded) {
                    $scope.parent.$loaded().then(loadArchive);
                } else {
                    loadArchive($scope.parent);
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

                scope.$watch('rawLogsContent', function(log) {
                    var value = concatLogChildren(log),
                        html = ansi_up.ansi_to_html(value);

                    $(element).html(html).trigger('updated-html');
                });
            }
        };
    })
    .directive('followScroll', function() {
        return {
            link: function(scope, element, attributes) {
                var scrollToTheBottom = function() {
                    element.scrollTop(element[0].scrollHeight);
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
    });
