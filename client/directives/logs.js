angular.module('logstream').directive('logs', ['RecursionHelper', function(RecursionHelper) {
    return {
        restrict: 'E',
        scope: {
            logId: '='
        },
        templateUrl: 'client/views/logs/logs.ng.html',
        controller: function ($scope, $meteor) {
            $scope.displayChildrenOf = [];
            $scope.toggleChildrenDisplay = function(log) {
                $scope.displayChildrenOf[log._id] = !$scope.displayChildrenOf[log._id];
            };

            $scope.logs = $meteor.collection(function() {
                return Logs.find({
                    parent: $scope.logId
                });
            });
        },
        compile: function(element) {
            return RecursionHelper.compile(element);
        }
    }
}]);
