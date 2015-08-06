'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowListController', function($scope, $remoteResource, FlowRepository) {
        $remoteResource.load('flows', FlowRepository.findAll()).then(function(flows) {
            $scope.flows = flows;
        });
    });
