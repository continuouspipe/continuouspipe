'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowAlertsController', function ($rootScope, $scope, $state, $q, ProjectAlertsRepository, AlertsRepository, AlertManager, flow, project) {
        $scope.alerts = [];

        function alertAlreadyExists(collection, alert) {
            return collection.filter(function(collectionAlert) {
                return collectionAlert.message === alert.message;
            }).length > 0;
        }

        function uniqueAlerts(alerts) {
            var uAlerts = [];

            alerts.forEach(function (alert) {
                if (alertAlreadyExists(uAlerts, alert)) {
                    return;
                }

                uAlerts.push(alert);
            });

            return uAlerts;
        }

        $scope.loadAlerts = function () {
            $q.all([
                AlertsRepository.findByFlow(flow),
                ProjectAlertsRepository.findByProject(project)
            ]).then(function(alertsCollection) {
                $scope.alerts = uniqueAlerts(
                    [].concat.apply([], alertsCollection)
                );
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
