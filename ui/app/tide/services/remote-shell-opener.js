'use strict';

angular.module('continuousPipeRiver')
    .service('RemoteShellOpener', function($mdDialog) {
        this.open = function(environment, component) {
            $mdDialog.show({
                controller: function($scope, $mdDialog) {
                    $scope.component = component;
                    $scope.environment = environment;

                    $scope.opened = $scope.cancel = function() {
                        $mdDialog.cancel();
                    };
                },
                templateUrl: 'tide/views/remote-shell-dialog.html',
                parent: angular.element(document.body),
                clickOutsideToClose: true
            });
        };
    });
