'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowListController', function($scope, $remoteResource, FlowRepository) {
        var controller = this;

        this.loadFlows = function() {
            $remoteResource.load('flows', FlowRepository.findAll()).then(function (flows) {
                $scope.flows = flows;
            });
        };

        $scope.deleteFlow = function(flow) {
            swal({
                title: "Are you sure?",
                text: "You will not be able to recover this flow!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                closeOnConfirm: false
            }, function() {
                FlowRepository.remove(flow).then(function() {
                    swal("Deleted!", "Flow successfully deleted.", "success");

                    controller.loadFlows();
                }, function() {
                    swal("Error !", "An unknown error occured while deleting flow", "error");
                });
            });
        };

        this.loadFlows();
    });
