'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowDashboardController', function ($scope, $remoteResource, $q, flow, $firebaseArray, $authenticatedFirebaseDatabase, PipelineRepository, PreferedPipelineStorage) {
        $scope.flow = flow;

        $scope.selectPipeline = function(pipeline) {
            $scope.selectedPipeline = pipeline;

            PreferedPipelineStorage.saveForFlow(flow.uuid, pipeline.uuid);
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

            var indexOfPipeline = function(pipelines, pipelineUuid) {
                for (var i = 0; i < pipelines.length; i++) {
                    if (pipelines[i].uuid == pipelineUuid) {
                        return i;
                    }
                }

                return -1;
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
                var preferredPipeline = PreferedPipelineStorage.getForFlow(flow.uuid),
                    pipelineIndex = 
                        preferredPipeline ? indexOfPipeline($scope.pipelines, preferredPipeline) :
                        ($scope.pipelines.length ? 0 : -1)
                    ;

                if (pipelineIndex !== -1) {
                    $scope.selectPipeline($scope.pipelines[pipelineIndex]);
                }
            });
        }).then(function () {
            $scope.isLoading = false;
        }));
    })
    .service('PreferedPipelineStorage', function() {
        var getPreferenceMapping = function() {
            try {
                return JSON.parse(localStorage.getItem('prefered_pipelines')) || {};
            } catch (e) {
                return {};
            }
        };

        var savePreferenceMapping = function(mapping) {
            localStorage.setItem('prefered_pipelines', JSON.stringify(mapping));
        };

        this.getForFlow = function(flowUuid) {
            return getPreferenceMapping()[flowUuid];
        };

        this.saveForFlow = function(flowUuid, pipelineUuid) {
            var mapping = getPreferenceMapping();
            mapping[flowUuid] = pipelineUuid;

            savePreferenceMapping(mapping);
        };
    })
;
