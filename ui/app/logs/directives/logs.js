angular.module('continuousPipeRiver')
    .directive('logs', ['RecursionHelper', '$http', function(RecursionHelper, $http) {
        return {
            restrict: 'E',
            scope: {
                parent: '=',
                level: '@',
                scope: '@',
                follow: '@'
            },
            templateUrl: 'logs/views/logs.ng.html',
            controller: ['$scope', function ($scope) {
                $scope.follow = true;
                $scope.isFullscreen = {
                    text: 'Expand',
                    enabled: false
                };
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

                $scope.fullscreen = function(enabled) {
                    if(!enabled) {
                        $scope.isFullscreen.text = 'Exit fullscreen',
                        $scope.isFullscreen.enabled = true;
                    } else {
                        $scope.isFullscreen.text = 'Expand',
                        $scope.isFullscreen.enabled = false;
                    }
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
                            {type: 'text', status: 'error', contents: 'Unable to load the logs from the archive'}
                        ];
                    });
                };

                $scope.$watch('parent.archived', function(archived) {
                    if (archived) {
                        loadArchive();
                    }
                });

                if ($scope.parent && $scope.parent.$loaded) {
                    $scope.parent.$loaded().then(loadArchive);
                } else {
                    loadArchive();
                }
            }],
            compile: function(element) {
                return RecursionHelper.compile(element);
            }
        };
    }])
    .directive('proxy', function() {
        return {
            restrict: 'E',
            scope: {
                log: '=',
                parent: '=',
                template: '@',
                scope: '@',
                follow: '@'
            },
            template: '<ng-include src="template" />',
            controller: ['$scope', function ($scope) {
                $scope.fullscreen = $scope.$parent.fullscreen;
                $scope.isFullscreen = $scope.$parent.isFullscreen;
            }]
        };
    })
;
