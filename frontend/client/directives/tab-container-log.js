angular.module('logstream')
    .directive('insertLogChildrenInScope', ['LogRepository', function(LogRepository) {
        return {
            scope: false,
            restrict: 'A',
            controller: ['$scope', function($scope) {
                $scope.children = LogRepository.findByParentId($scope.log._id);
            }]
        };
    }]);
