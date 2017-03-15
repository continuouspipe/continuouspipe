'use strict';

angular.module('continuousPipeRiver')
    .directive('pipelineOverview', function ($authenticatedFirebaseDatabase, $firebaseArray) {
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
                $authenticatedFirebaseDatabase.get($scope.flow).then(function (database) {
                    var lastTides = $firebaseArray(
                        database.ref()
                            .child('flows/' + $scope.flow.uuid + '/tides/by-pipelines/' + $scope.pipeline.uuid)
                            .orderByChild('creation_date')
                            .limitToLast(40)
                    );

                    lastTides.$watch(function(e) {
                        var matchingTides = lastTides.filter(function(element) {
                            return !$scope.branch || element.code_reference.branch == $scope.branch;
                        });

                        // The last tide is the last in the array because of the order
                        // coming from Firebase.
                        $scope.pipeline.last_tide = matchingTides[matchingTides.length - 1];
                    });
                });

                $scope.deletePipeline = function (pipelineId) {
                    PipelineRepository.delete($scope.flow.uuid, pipelineId);
                };
            }
        };
    })
    ;
