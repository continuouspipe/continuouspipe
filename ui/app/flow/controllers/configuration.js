'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowConfigurationController', function($scope, $remoteResource, $mdToast, TideRepository, EnvironmentRepository, FlowRepository, flow) {
        $scope.aceOption = {
            mode: 'yaml'
        };

        $scope.save = function() {
            $scope.isLoading = true;

            FlowRepository.update(flow).then(function() {
                $mdToast.show($mdToast.simple()
                    .textContent('Configuration successfully saved!')
                    .position('top')
                    .hideDelay(3000)
                    .parent($('md-content.configuration-content'))
                );
            }, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while creating flow";
                swal("Error !", message, "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };

        $scope.flow = flow;
    });
