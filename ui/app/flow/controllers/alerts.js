'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowAlertsController', function ($scope, $state, ProjectAlertsRepository, AlertsRepository, AlertManager, flow, project) {
        $scope.alerts = [];

        ProjectAlertsRepository.findByProject(project).then(function (alerts) {
            alerts.forEach(function (alert) {
                $scope.alerts.push(alert);
            });
        });

        AlertsRepository.findByFlow(flow).then(function (alerts) {
            alerts.forEach(function (alert) {
                $scope.alerts.push(alert);
            });
        });

        $scope.actionAlert = function (alert) {
            AlertManager.open(alert);
        };

        $scope.showAlerts = function () {
            AlertManager.showAll($scope.alerts);
        };
    });
