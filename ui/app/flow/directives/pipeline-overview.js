'use strict';

angular.module('continuousPipeRiver')
    .directive('pipelineOverview', function() {
        return {
            restrict: 'E',
            scope: {
                pipeline: '=',
                flow: '='
            },
            templateUrl: 'flow/views/directives/pipeline-overview.html',
            controller: function($scope, PipelineRepository) {
                $scope.deletePipeline = function (pipelineId) {
                    PipelineRepository.delete($scope.flow.uuid, pipelineId);
                };
            }
        };
    })
;
