'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowListController', function($scope, $remoteResource, FlowRepository, team) {
        $remoteResource.load('flows', FlowRepository.findByTeam(team)).then(function (flows) {
            $scope.flows = flows;
        });
    });
