'use strict';

angular.module('continuousPipeRiver')
    .controller('AccountsController', function($scope, AccountRepository, AUTHENTICATOR_API_URL) {

        $scope.connectAccountUrl = AUTHENTICATOR_API_URL + '/account/connected-accounts';
        $scope.accounts = [];

        AccountRepository.findMine().then(function(accounts) {
            $scope.accounts = accounts;
        }, function(error) {
            swal("Error !", $http.getError(error) || "An unknown error occured while loading your connected accounts", "error");
        });

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
                AccountRepository.unlinkAccount(account.uuid)
                    .then(function(res) {
                        removeAccount(account.uuid);
                    });
            });
        };

        $scope.connectAccount = function (type) {
            AccountRepository.connectAccount(type);
        };

        var removeAccount = function(accountUuid) {
            $scope.accounts = $scope.accounts.filter(function(account) {
                return account.uuid != accountUuid;
            });
        }

    });
