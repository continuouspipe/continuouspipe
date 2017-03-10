'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowAlertsController', function ($rootScope, $scope, $state, ProjectAlertsRepository, AlertsRepository, AlertManager, flow, project) {
        $scope.alerts = [];

        function filterUniqueAlerts(alerts) {
            alerts
                .filter(function (alert) {
                    return !$scope.alerts.filter(function (inScope) {
                        return inScope.message === alert.message;
                    }).length;
                })
                .forEach(function (alert) {
                    $scope.alerts.push(alert);
                });
        }

        $scope.loadAlerts = function () {
            ProjectAlertsRepository.findByProject(project).then(function (alerts) {
                filterUniqueAlerts(alerts);
            });

            AlertsRepository.findByFlow(flow).then(function (alerts) {
                filterUniqueAlerts(alerts);
            });
        };

        $scope.actionAlert = function (alert) {
            AlertManager.open(alert);
        };

        $scope.showAlerts = function () {
            AlertManager.showAll($scope.alerts);
        };

        $rootScope.$on('configuration-saved', $scope.loadAlerts);
        $rootScope.$on('visibility-changed', $scope.loadAlerts);

        $scope.loadAlerts();
    });
