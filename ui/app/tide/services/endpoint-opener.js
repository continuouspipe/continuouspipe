'use strict';

angular.module('continuousPipeRiver')
    .service('EndpointOpener', function($mdDialog) {
        this.open = function(endpoint) {
            $mdDialog.show({
                controller: function($scope, $mdDialog) {
                    $scope.endpoint = endpoint;

                    $scope.cancel = function() {
                        $mdDialog.cancel();
                    };
                },
                templateUrl: 'tide/views/endpoint-dialog.html',
                parent: angular.element(document.body),
                clickOutsideToClose: true
            });
        };
    });
