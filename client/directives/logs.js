angular.module('logstream').directive('logs', ['RecursionHelper', function(RecursionHelper) {
    return {
        restrict: 'E',
        scope: {
            logId: '='
        },
        templateUrl: 'client/views/logs/logs.ng.html',
        controller: function ($scope, $meteor) {
            $scope.toggleChildrenDisplay = function(log) {
                log.displayChildren = !log.displayChildren;
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
