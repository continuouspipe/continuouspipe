'use strict';

angular.module('continuousPipeRiver')
    .service('PipelineRepository', function ($http, RIVER_API_URL) {
        this.delete = function (flowId, pipelineId) {
            return $http({
                method: 'DELETE',
                url: RIVER_API_URL + '/flows/' + flowId + '/pipeline/' + pipelineId
            });
        };
    });
