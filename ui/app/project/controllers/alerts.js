'use strict';

angular.module('continuousPipeRiver')
    .controller('ProjectAlertsController', function ($scope, $state, ProjectAlertsRepository, project) {
        ProjectAlertsRepository.findByProject(project).then(function (alerts) {
            $scope.alerts = alerts;
        });

        $scope.actionAlert = function (action) {
            if (action.type == 'link') {
                window.open(action.href, '_blank');
            } else if (action.type == 'state') {
                $state.go(action.href);
            }
        };
    });
