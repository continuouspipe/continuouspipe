'use strict';

angular.module('continuousPipeRiver')
    .service('PipelineRepository', function ($http, RIVER_API_URL, $mdToast) {
        this.delete = function (flowId, pipelineId) {

            var results = {
                '204': 'Pipeline Successfully deleted',
                '404': 'Could not find specified pipeline',
                '400': 'Pipeline could not be deleted'
            };

            swal({
                title: 'Are you sure?',
                text: "The pipeline will be deleted. You won't be able to revert this!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DD6B55',
                confirmButtonText: 'Yes, delete it!'
            }).then(function() {
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
            }).catch(swal.noop);
        };
    });
