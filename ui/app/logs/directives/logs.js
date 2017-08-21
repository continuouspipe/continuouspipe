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
        }
    }])
    .directive('proxy', function() {
        return {
            restrict: 'E',
            scope: {
                log: '=',
                template: '@',
                follow: '@'
            },
            template: '<ng-include src="template" />'
        }
    })
;
