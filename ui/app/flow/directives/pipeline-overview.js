'use strict';

angular.module('continuousPipeRiver')
    .directive('pipelineOverview', function($authenticatedFirebaseDatabase, $firebaseArray) {
        return {
            restrict: 'E',
            scope: {
                pipeline: '=',
                flow: '=',
                disableDeletion: '@'
            },
            templateUrl: 'flow/views/directives/pipeline-overview.html',
            controller: function($scope, PipelineRepository) {
                $authenticatedFirebaseDatabase.get($scope.flow).then(function (database) {
                    $scope.pipeline.lastTides = $firebaseArray(
                        database.ref()
                            .child('flows/' + $scope.flow.uuid + '/tides/by-pipelines/' + $scope.pipeline.uuid)
                            .orderByChild('creation_date')
                            .limitToLast(1)
                    );
                });

                $scope.deletePipeline = function (pipelineId) {
                    PipelineRepository.delete($scope.flow.uuid, pipelineId);
                };
            }
        };
    })
;
