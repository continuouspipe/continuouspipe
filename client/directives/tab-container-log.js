angular.module('logstream')
    .directive('insertLogChildrenInScope', function(LogRepository) {
        return {
            scope: false,
            restrict: 'A',
            controller: function($scope) {
                $scope.children = LogRepository.findByParentId($scope.log._id);
            }
        };
    });
