'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowDashboardController', function ($scope, $remoteResource, $q, flow, $firebaseArray, $authenticatedFirebaseDatabase, PipelineRepository, pipelineInfo) {
        $scope.flow = flow;
        $scope.tidesPerPipeline = [];
        $scope.isLoading = true;
        $scope.tides = [];

        $scope.checkName = function(pipelineName, checkAgainst) {

            return function(tide) {
                return tide.pipeline.uuid === checkAgainst.uuid;
            }
            
        }

        $scope.chosenPipeline = function(pipelines) {
            const newPipelines = pipelines.sort((a, b) => a.name !== b.name ? a.name < b.name ? -1 : 1 : 0);
            if (pipelineInfo.id == undefined) {
                return newPipelines[0];
            } else {
                for(const pipeline in pipelines) {
                    if (pipelines[pipeline].uuid === pipelineInfo.id) {
                        return pipelines[pipeline];
                    } 
                }
            }
        }

        var mergeTidesIntoOneArray = function () {
            var tides = [];

            for (var pipelineUuid in $scope.tidesPerPipeline) {
                $scope.tidesPerPipeline[pipelineUuid].forEach(function (tide) {
                    tides.push(tide);
                });
            }

            $scope.tides = tides;
        };

        var watchPipelineTides = function (database) {
            var promises = [];

            $scope.pipelines.forEach(function (pipeline) {
                $scope.tidesPerPipeline[pipeline.uuid] = $firebaseArray(
                    database.ref()
                        .child('flows/' + flow.uuid + '/tides/by-pipelines/' + pipeline.uuid)
                        .orderByChild('creation_date')
                        .limitToLast(10)
                );

                $scope.tidesPerPipeline[pipeline.uuid].$watch(mergeTidesIntoOneArray);

                promises.push($scope.tidesPerPipeline[pipeline.uuid].$loaded);
            });

            return $q.all(promises);
        };

        $remoteResource.load('tides', $authenticatedFirebaseDatabase.get(flow).then(function (database) {
            $scope.pipelines = $firebaseArray(
                database.ref().child('flows/' + flow.uuid + '/pipelines')
            );

            return $scope.pipelines.$loaded(function () {
                $scope.pipelines.$watch(function () {
                    watchPipelineTides(database);
                });

                return watchPipelineTides(database);
            });
        }).then(function () {
            $scope.isLoading = false;
        }));
    })

    .factory('pipelineInfo', 
        function() {
            const factory = {};

            factory.changePipeline = function(pipelineId) {
                factory.id = pipelineId;
            }

            return factory;
        } 
    );
