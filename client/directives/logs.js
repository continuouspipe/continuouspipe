angular.module('logstream')
    .directive('logs', ['RecursionHelper', 'LogRepository', function(RecursionHelper, LogRepository) {
        return {
            restrict: 'E',
            scope: {
                logId: '=',
                counter: '='
            },
            templateUrl: 'client/views/logs/logs.ng.html',
            controller: ['$scope', function ($scope) {
                $scope.childrenCounter = {count: 0};
                $scope.displayChildrenOf = [];
                $scope.toggleChildrenDisplay = function(log) {
                    $scope.displayChildrenOf[log._id] = !$scope.displayChildrenOf[log._id];
                };

                $scope.logs = LogRepository.findByParentId($scope.logId);

                $scope.$watch('logs', function(logs) {
                    if ($scope.counter) {
                        $scope.counter.count = logs.length;
                    }

                    logs.forEach(function(log) {
                        if ($scope.displayChildrenOf[log._id] === undefined) {
                            $scope.displayChildrenOf[log._id] = true;
                        }
                    });
                });
            }],
            compile: function(element) {
                return RecursionHelper.compile(element);
            }
        }
    }]);
