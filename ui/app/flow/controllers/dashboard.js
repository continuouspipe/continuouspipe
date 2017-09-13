'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowDashboardController', function ($scope, $remoteResource, $q, flow, $firebaseArray, $authenticatedFirebaseDatabase, PipelineRepository) {
        $scope.flow = flow;

        $scope.selectPipeline = function(pipeline) {
            $scope.selectedPipeline = pipeline;
        };

        $scope.isLoading = true;
        $remoteResource.load('pipelines', $authenticatedFirebaseDatabase.get(flow).then(function (database) {
            var loadPipelinesTide = function(pipeline, limit) {
                var reference = database.ref()
                    .child('flows/' + flow.uuid + '/tides/by-pipelines/' + pipeline.uuid)
                    .orderByChild('creation_date')
                    .limitToLast(limit);

                return {
                    ref: reference,
                    array: $firebaseArray(reference),
                    limit: limit
                };
            };

            var tidesPerPipelineCache = {};
            $scope.tidesForPipeline = function(pipeline) {
                if (!(pipeline.uuid in tidesPerPipelineCache)) {
                    tidesPerPipelineCache[pipeline.uuid] = loadPipelinesTide(pipeline, 10);
                }

                return tidesPerPipelineCache[pipeline.uuid].array;
            };

            $scope.loadMoreTides = function(pipeline) {
                var view = loadPipelinesTide(
                    pipeline,
                    tidesPerPipelineCache[pipeline.uuid].limit + 20
                );

                $scope.isLoadingMore = true;
                view.array.$loaded(function() {
                    tidesPerPipelineCache[pipeline.uuid] = view;
                    $scope.isLoadingMore = false;
                });
            };
            
            // Load pipelines
            $scope.pipelines = $firebaseArray(
                database.ref().child('flows/' + flow.uuid + '/pipelines')
            );

            return $scope.pipelines.$loaded(function() {
                if ($scope.pipelines.length) {
                    $scope.selectPipeline($scope.pipelines[0]);
                }
            });
        }).then(function () {
            $scope.isLoading = false;
        }));
    })
;
