'use strict';

angular.module('continuousPipeRiver')
    .controller('ProjectRegistryCredentialsController', function($scope, $http, $remoteResource, RegistryCredentialsRepository, user, project) {
        var controller = this;

        this.loadCredentials = function() {
            $remoteResource.load('credentials', RegistryCredentialsRepository.findAll()).then(function (credentials) {
                $scope.credentials = credentials;
            });
        };

        $scope.deleteCredentials = function(credentials) {
            swal({
                title: "Are you sure?",
                text: "You will not be able to cancel this action!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                closeOnConfirm: false
            }, function() {
                RegistryCredentialsRepository.remove(credentials).then(function() {
                    swal("Deleted!", "Credentials successfully deleted.", "success");

                    controller.loadCredentials();
                }, function(error) {
                    swal("Error !", $http.getError(error) || "An unknown error occured while deleting credentials", "error");
                });
            });
        };
        
        $scope.isAdmin = user.isAdmin(project);

        this.loadCredentials();

        $scope.changeVisibility = function(registry, visibility) {
            swal({
                title: "Are you sure?",
                text: "You will change the visibility of the registry to \""+visibility+"\"",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, change it!",
                closeOnConfirm: false
            }, function() {
                RegistryCredentialsRepository.changeVisibility(registry, visibility).then(function() {
                    controller.loadCredentials();
                }, function(error) {
                    swal("Error !", $http.getError(error) || "An unknown error occurred while changing the visibility of the registry", "error");
                });
            });
        };
    });
