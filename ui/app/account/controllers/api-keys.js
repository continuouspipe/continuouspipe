'use strict';

angular.module('continuousPipeRiver')
    .controller('ApiKeysController', function($scope, $state, $remoteResource, $http, user, UserRepository) {

        $scope.apiKeys = [];
        $scope.apiKey = {};

        var load = function() {
            $remoteResource.load('apiKeys', UserRepository.findApiKeysByUsername(user.username)).then(function (apiKeys) {
                $scope.apiKeys = apiKeys;
            });
        };

        $scope.create = function (apiKey) {
            $scope.isLoading = true;
            UserRepository.createApiKey(user.username, apiKey).then(function () {
                load();

                Intercom('trackEvent', 'created-api-key', {
                    apiKey: apiKey
                });
            }, function (error) {
                 swal("Error !", $http.getError(error) || "An unknown error occured while create the project", "error");
            })['finally'](function () {
                 $scope.isLoading = false;
            });
        };

        $scope.deleteKey = function (apiKey) {
            swal({
                title: "Are you sure?",
                text: "The "+ apiKey.api_key +" key will be deleted.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                closeOnConfirm: true
            }, function() {
                UserRepository.deleteApiKey(user.username, apiKey).then(function() {
                    swal("Deleted!", "API key successfully deleted.", "success");

                    load();
                }, function(error) {
                    swal("Error !", $http.getError(error) || "An unknown error occurred while deleting the API key", "error");
                });
            });
        };

        load();
    });
