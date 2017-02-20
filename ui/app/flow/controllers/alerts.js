'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowAlertsController', function ($scope, $state, ProjectAlertsRepository, AlertsRepository, flow, project) {
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

        $scope.actionAlert = function (action) {
            if (action.type == 'link') {
                window.open(action.href, '_blank');
            } else if (action.type == 'state') {
                $state.go(action.href);
            }
        };
    });
