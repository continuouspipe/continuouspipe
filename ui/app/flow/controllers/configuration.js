'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowConfigurationController', function($scope, $remoteResource, $mdToast, $state, TideRepository, EnvironmentRepository, FlowRepository, flow) {
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

        $scope.delete = function() {
            swal({
                title: "Are you sure?",
                text: "This will remove the flow and its tide history",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                closeOnConfirm: false
            }, function() {
                FlowRepository.remove(flow).then(function() {
                    swal("Deleted!", "Cluster successfully deleted.", "success");

                    $state.go('flows');
                }, function() {
                    swal("Error !", "An unknown error occurred while deleting the flow", "error");
                });
            });
        };

        $scope.flow = flow;
    });
