angular.module('logstream')
    .directive('logs', ['RecursionHelper', 'LogRepository', function(RecursionHelper, LogRepository) {
        return {
            restrict: 'E',
            scope: {
                logId: '='
            },
            templateUrl: 'client/views/logs/logs.ng.html',
            controller: ['$scope', function ($scope) {
                $scope.displayChildrenOf = [];
                $scope.toggleChildrenDisplay = function(log) {
                    $scope.displayChildrenOf[log._id] = !$scope.displayChildrenOf[log._id];
                };

                $scope.logs = LogRepository.findByParentId($scope.logId);
            }],
            compile: function(element) {
                return RecursionHelper.compile(element);
            }
        }
    }]);
