'use strict';

angular.module('continuousPipeRiver')
    .controller('ProjectAlertsController', function ($scope, $state, ProjectAlertsRepository, project, $mdDialog) {
        ProjectAlertsRepository.findByProject(project).then(function (alerts) {
            $scope.alerts = alerts;
            console.log(alerts[0]);
            for (var i = 0; i < 3; i++) {
                $scope.alerts.push(alert);
            }
        });

        $scope.actionAlert = function (action) {
            if (action.type == 'link') {
                window.open(action.href, '_blank');
            } else if (action.type == 'state') {
                $state.go(action.href);
            }
        };

        $scope.showAlerts = function () {
            $mdDialog.show({
                controller: function ($scope, $mdDialog) {
                    $scope.close = function () {
                        $mdDialog.cancel();
                    };
                },
                templateUrl: 'project/views/dialogs/alert-list.html',
                parent: angular.element(document.body),
                clickOutsideToClose: true,
                scope: $scope
            });
        };
    });
