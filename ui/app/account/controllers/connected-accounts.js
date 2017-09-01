'use strict';

angular.module('continuousPipeRiver')
    .controller('ConnectedAccountsController', function($scope, $remoteResource, $http, AccountRepository) {
        var load = function() {
            $remoteResource.load('accounts', AccountRepository.findMine()).then(function (accounts) {
                $scope.accounts = accounts;
            });
        };

        var handleError = function(error) {
            swal("Error !", $http.getError(error) || "An unknown error occured while unlinking the account", "error");
        };

        $scope.unlinkAccount = function (account) {
            swal({
                title: "Are you sure?",
                text: "The "+ account.type +" account will be removed.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, unlink it!",
                closeOnConfirm: true
            }, function() {
                AccountRepository.unlinkAccount(account.uuid).then(load, handleError);
            });
        };

        $scope.connectAccount = function (type) {
            AccountRepository.connectAccount(type);
        };

        load();
    });
