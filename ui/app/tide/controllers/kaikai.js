'use strict';

angular.module('continuousPipeRiver')
    .controller('KaiKaiController', function($scope, tide) {
        $scope.tide = tide;

        console.log(tide);
    })
    .controller('KaiKaiHeaderController', function($scope, tide) {
        $scope.tide = tide;
    })
    .controller('KaiKaiLogsController', function($scope, tide) {
        $scope.tide = tide;
    });

