'use strict';

angular.module('continuousPipeRiver')
    .controller('AccountsController', function($scope, AccountRepository, AUTHENTICATOR_API_URL) {

        $scope.connectAccountUrl = AUTHENTICATOR_API_URL + '/account/connected-accounts';
        $scope.accounts = [];
        $scope.isAdmin = true;

        AccountRepository.findMine().then(function(accounts) {
            $scope.accounts = accounts;
        }, function(error) {
            swal("Error !", $http.getError(error) || "An unknown error occured while loading your Google accounts", "error");
        });

        $scope.unlinkAccount = function (account) {
            AccountRepository.unlinkAccount(account.uuid)
                .then(function(res) {
                    removeAccount(account.uuid);
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
