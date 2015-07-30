angular.module('logstream')
    .controller('LogCtrl', ['$scope', '$meteor', '$stateParams', function ($scope, $meteor, $stateParams) {
        $scope.logId = $stateParams.logId;
    }]);
