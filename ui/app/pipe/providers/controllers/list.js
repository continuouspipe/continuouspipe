'use strict';

angular.module('continuousPipeRiver')
    .controller('PipeProviderListController', function($scope, $remoteResource, ProviderRepository) {
        var controller = this;

        this.loadProviders = function() {
            $remoteResource.load('providers', ProviderRepository.findAll()).then(function (providers) {
                $scope.providers = providers;
            });
        };

        $scope.deleteProvider = function(provider) {
            swal({
                title: "Are you sure?",
                text: "You will not be able to recover this provider!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                closeOnConfirm: false
            }, function() {
                ProviderRepository.remove(provider).then(function() {
                    swal("Deleted!", "Provider successfully deleted.", "success");

                    controller.loadProviders();
                }, function() {
                    swal("Error !", "An unknown error occured while deleting provider", "error");
                });
            });
        };

        this.loadProviders();
    });
