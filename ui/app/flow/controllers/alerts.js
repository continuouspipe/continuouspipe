'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowAlertsController', function($scope, AlertsRepository, flow) {
    	AlertsRepository.findByFlow(flow).then(function(alerts) {
    		console.log(alerts);
    		
    		$scope.alerts = alerts;
    	});

    	$scope.actionAlert = function(action) {
			window.open(action.href, '_blank');
    	};
    });
