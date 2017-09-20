'use strict';

angular.module('continuousPipeRiver')
    .directive('pipelineOverview', function ($authenticatedFirebaseDatabase, $firebaseArray, $mdToast) {
        return {
            restrict: 'E',
            scope: {
                pipeline: '=',
                flow: '=',
                branch: '=',
                disableDeletion: '@',
                headline: '@'
            },
            templateUrl: 'flow/views/directives/pipeline-overview.html',
            controller: function ($scope, PipelineRepository) {
                $scope.isLoading = true;
                $authenticatedFirebaseDatabase.get($scope.flow).then(function (database) {
                    var lastTides = $firebaseArray(
                        database.ref()
                            .child('flows/' + $scope.flow.uuid + '/tides/by-pipelines/' + $scope.pipeline.uuid)
                            .orderByChild('creation_date')

                            // Limit to 100 so we have high chances to find a matching tide
                            // while we don't filter without so 1 is enough.
                            .limitToLast($scope.branch ? 100 : 1)
                    );

                    lastTides.$watch(function(e) {
                        var matchingTides = lastTides.filter(function(element) {
                            return !$scope.branch || element.code_reference.branch == $scope.branch;
                        });

                        // The last tide is the last in the array because of the order
                        // coming from Firebase.
                        $scope.pipeline.last_tide = matchingTides[matchingTides.length - 1];
                    });

                    lastTides.$loaded().then(function() {
                        $scope.isLoading = false;
                    });
                });

                $scope.deletePipeline = function (pipelineId) {
                    swal({
                        title: 'Are you sure?',
                        text: "The pipeline will be deleted. You won't be able to revert this!",
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#DD6B55',
                        confirmButtonText: 'Yes, delete it!'
                    }).then(function() {
                        var results = {
                            '204': 'Pipeline Successfully deleted',
                            '404': 'Could not find specified pipeline',
                            '400': 'Pipeline could not be deleted'
                        };
                        
                        PipelineRepository.delete($scope.flow.uuid, pipelineId).then(function(response) {
                            $mdToast.show($mdToast.simple()
                                    .textContent(results[response.status.toString()])
                                    .position('top')
                                    .hideDelay(3000)
                                    .parent($('#content')));
                        }).catch(function(response) {
                            console.log(response)
                            swal("Error !", response.data.message || results[response.status.toString()], "error");
                        });
                    }).catch(swal.noop);
                };
            }
        };
    })
;