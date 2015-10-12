'use strict';

angular.module('continuousPipeRiver')
    .controller('KaiKaiController', function($scope, tide) {
        $scope.tide = tide;
    })
    .controller('KaiKaiHeaderController', function($scope, tide, summary) {
        $scope.tide = tide;
        $scope.summary = summary;
    })
    .controller('KaiKaiServiceController', function($scope, tide, summary, $stateParams) {
        $scope.tide = tide;
        $scope.summary = summary;
        $scope.service = summary.deployed_services[$stateParams.name];
        $scope.isPublic = !!$scope.service.public_endpoint;
    })
    .controller('KaiKaiLogsController', function($scope, tide) {
        $scope.tide = tide;
    });

