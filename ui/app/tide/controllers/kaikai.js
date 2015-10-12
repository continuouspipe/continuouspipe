'use strict';

angular.module('continuousPipeRiver')
    .controller('KaiKaiController', function($scope, $state, tide, summary) {
        if (summary.status == 'running' || !summary.deployed_services) {
            $state.go('kaikai.logs', {uuid: tide.uuid});
        } else {
            var publicServices = [];
            for (var serviceName in summary.deployed_services) {
                if (summary.deployed_services[serviceName].public_endpoint) {
                    publicServices.push(serviceName);
                }
            }

            if (publicServices.length > 0) {
                $state.go('kaikai.service', {uuid: tide.uuid, name: publicServices[0]});
            }
        }
    })
    .controller('KaiKaiHeaderController', function($scope, tide, summary) {
        $scope.tide = tide;
        $scope.summary = summary;

        var reloadIfRunning = function() {
            if ($scope.summary.status == 'running') {
                setTimeout(function () {
                    $scope.summary.$get({uuid: tide.uuid});

                    reloadIfRunning();
                }, 5000);
            }
        };

        reloadIfRunning();
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

