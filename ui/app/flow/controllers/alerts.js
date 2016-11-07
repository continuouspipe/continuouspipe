'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowAlertsController', function($scope, $state, AlertsRepository, flow) {
        AlertsRepository.findByFlow(flow).then(function(alerts) {
            console.log(alerts);
            
            $scope.alerts = alerts;
        });

        $scope.actionAlert = function(action) {
            if (action.type == 'link') {
                window.open(action.href, '_blank');
            } else if (action.type == 'state') {
                $state.go(action.href);
            }
        };
    });
