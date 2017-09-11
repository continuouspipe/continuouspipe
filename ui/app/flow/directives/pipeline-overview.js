'use strict';

angular.module('continuousPipeRiver')
    .directive('pipelineOverview', function ($authenticatedFirebaseDatabase, $firebaseArray, pipelineInfo) {
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
            controller: function ($scope, PipelineRepository, pipelineInfo) {
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
                    PipelineRepository.delete($scope.flow.uuid, pipelineId);
                };

                $scope.updatePipeline = function (pipeline) {
                    $scope.$parent.pipelineSelected.selected = pipeline;
                    pipelineInfo.changePipeline(pipeline.uuid);
                };
            }
        };
    })
;
