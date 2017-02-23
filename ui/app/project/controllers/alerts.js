'use strict';

angular.module('continuousPipeRiver')
    .controller('ProjectAlertsController', function ($scope, $state, ProjectAlertsRepository, AlertManager, project) {
        ProjectAlertsRepository.findByProject(project).then(function (alerts) {
            $scope.alerts = alerts;
        });

        $scope.actionAlert = function (alert) {
            AlertManager.open(alert);
        };

        $scope.showAlerts = function () {
            AlertManager.showAll($scope.alerts);
        };
    })
    .service('AlertManager', function($state, $mdDialog, $rootScope) {
        this.open = function(alert) {
            var action = alert.action;

            if (action.type == 'link') {
                window.open(action.href, '_blank');
            } else if (action.type == 'state') {
                $state.go(action.href);
            }
        };

        this.showAll = function(alerts) {
            var scope = $rootScope.$new();
            scope.alerts = alerts;

            $mdDialog.show({
                controller: function ($scope, $mdDialog, AlertManager) {
                    $scope.close = function () {
                        $mdDialog.cancel();
                    };

                    $scope.actionAlert = function(alert) {
                        AlertManager.open(alert);

                        $scope.close();
                    };
                },
                templateUrl: 'project/views/dialogs/alert-list.html',
                parent: angular.element(document.body),
                clickOutsideToClose: true,
                scope: scope
            });
        };
    });
