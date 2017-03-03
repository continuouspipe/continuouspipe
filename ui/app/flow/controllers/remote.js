'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowRemoteController', function ($rootScope, $state, $stateParams, $scope, $remoteResource, $mdToast, RemoteRepository, TideRepository, flow) {
        $scope.token = '';
        $scope.environments = [];
        $scope.showForm = false;
        $scope.branchName = 'cpdev/' + $rootScope.user.username;
        $scope.stagedEnvironment = $rootScope.user.username + ' environment';

        RemoteRepository.getDevEnvironments(flow).then(function (environments) {
            $scope.environments = environments;
        });

        $scope.stageEnvironment = function () {
            $scope.showForm = true;
        };

        $scope.createEnvironment = function () {
            RemoteRepository
                .createDevEnvironment($scope.stagedEnvironment, flow)
                .then(function (environment) {
                    $scope.environments.unshift(environment);
                    $scope.showForm = false;
                });
        };

        $scope.copyToken = function () {
            try {
                $('#envToken').select();
                document.execCommand('copy');
                $mdToast.showSimple('Token copied to clipboard')
            } catch (err) {
                //console.log('Oops, unable to copy');
            }

            window.getSelection().removeAllRanges();
        };

        $scope.getToken = function () {
            RemoteRepository.issueToken($scope.branchName, $stateParams.environment, flow).then(function (token) {
                $('input[name="branchName"]').prop('disabled', true);
                $scope.token = token.token;
            });
        };
    })
;
