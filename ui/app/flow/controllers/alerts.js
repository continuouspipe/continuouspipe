'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowAlertsController', function ($rootScope, $scope, $state, ProjectAlertsRepository, AlertsRepository, AlertManager, flow, project) {
        $scope.alerts = [];

        $scope.loadAlerts = function () {
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
        };

        $scope.actionAlert = function (alert) {
            AlertManager.open(alert);
        };

        $scope.showAlerts = function () {
            AlertManager.showAll($scope.alerts);
        };

        $rootScope.$on('configuration-saved', $scope.loadAlerts);
        $rootScope.$on('location-changed', $scope.loadAlerts);

        $scope.loadAlerts();
    });
