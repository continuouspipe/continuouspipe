'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowRemoteController', function ($rootScope, $state, $scope, $remoteResource, $mdToast, RemoteRepository, flow) {
        $scope.token = '';
        $scope.environments = [];
        $scope.branchName = 'cpdev/' + $rootScope.user.username;
        $scope.currentEnvironment = {};

        $scope.creationScreen = function () {
            $state.go({name: 'flows.create-remote'});
        };

        $scope.copyToken = function () {
            $('#envToken').select();

            try {
                document.execCommand('copy');
                $('[name="branchName"]').attr('disabled', true);
                $mdToast.showSimple('Token copied to clipboard')
            } catch (err) {
                //console.log('Oops, unable to copy');
            }

            window.getSelection().removeAllRanges();
        };


        RemoteRepository.getDevEnvironments(flow).then(function (environments) {
            if (environments.length === 0) $scope.creationScreen();
            $scope.environments = environments;
        });

        $scope.getToken = function () {
            var name = $rootScope.user.username + ' environment';
            RemoteRepository.createDevEnvironment(name, flow).then(function (environment) {
                $scope.currentEnvironment = environment;

                RemoteRepository.issueToken($scope.branchName, environment, flow).then(function (token) {
                    $scope.token = token.token;
                });
            });
        };

        $scope.delete = function (environment) {
        };
    })
;
