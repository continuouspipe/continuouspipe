'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowEnvironmentsController', function($scope, $remoteResource, TideRepository, EnvironmentRepository, flow) {
        $scope.flow = flow;

        var loadEnvironments = function() {
            $remoteResource.load('environments', EnvironmentRepository.findByFlow(flow)).then(function (environments) {
                $scope.environments = environments;
            });
        };

        $scope.delete = function(environment) {
            swal({
                title: "Are you sure?",
                text: "The environment "+environment.identifier+" won't be recoverable",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, remove it!",
                closeOnConfirm: false
            }, function() {
                EnvironmentRepository.delete(flow, environment).then(function () {
                    swal("Deleted!", "Environment successfully deleted.", "success");

                    loadEnvironments();
                }, function (error) {
                    var message = ((error || {}).data || {}).message || "An unknown error occured while deleting the environment";
                    swal("Error !", message, "error");
                });
            });
        };

        loadEnvironments();
    });
