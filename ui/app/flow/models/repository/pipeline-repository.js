'use strict';

angular.module('continuousPipeRiver')
    .service('PipelineRepository', function ($http, RIVER_API_URL, $mdToast) {
        this.delete = function (flowId, pipelineId) {

            var results = {
                '204': 'Pipeline Successfully deleted',
                '404': 'Could not find specified pipeline',
                '400': 'Pipeline could not be deleted'
            };

            $http({
                method: 'DELETE',
                url: RIVER_API_URL + '/flows/' + flowId + '/pipeline/' + pipelineId
            }).then(function success(response) {
                $mdToast.show($mdToast.simple()
                    .textContent(results[response.status.toString()])
                    .position('top')
                    .hideDelay(3000)
                    .parent($('#content')));
            }, function error(response) {                    
                swal("Error !", $http.getError(response) || results[response.status.toString()], "error");
            });
        };
    });
