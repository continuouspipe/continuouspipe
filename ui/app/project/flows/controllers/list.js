'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowListController', function($scope, $remoteResource, FlowRepository, project) {
        $remoteResource.load('flows', FlowRepository.findByProject(project)).then(function (flows) {
            $scope.flows = flows;
        });
    });
