'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowDashboardController', function($scope, $remoteResource, $q, flow, $firebaseArray, $authenticatedFirebaseDatabase) {
        $scope.flow = flow;
        $scope.tidesPerPipeline = [];
        $scope.isLoading = true;
        $scope.tides = [];

        var mergeTidesIntoOneArray = function() {
            var tides = [];

            for (var pipelineUuid in $scope.tidesPerPipeline) {
                $scope.tidesPerPipeline[pipelineUuid].forEach(function(tide) {
                    tides.push(tide);
                });
            }

            $scope.tides = tides;
        };

        var watchPipelineTides = function(database) {
            var promises = [];

            $scope.pipelines.forEach(function(pipeline) {
                if (pipeline.lastTides) {
                    return;
                }

                pipeline.lastTides = $firebaseArray(
                    database.ref()
                    .child('flows/'+flow.uuid+'/tides/by-pipelines/'+pipeline.uuid)
                    .orderByChild('creation_date')
                    .limitToLast(1)
                );

                $scope.tidesPerPipeline[pipeline.uuid] = $firebaseArray(
                    database.ref()
                    .child('flows/'+flow.uuid+'/tides/by-pipelines/'+pipeline.uuid)
                    .orderByChild('creation_date')
                    .limitToLast(10)
                );

                $scope.tidesPerPipeline[pipeline.uuid].$watch(mergeTidesIntoOneArray);

                promises.push($scope.tidesPerPipeline[pipeline.uuid].$loaded);
            });

            return $q.all(promises);
        };

        $remoteResource.load('tides', $authenticatedFirebaseDatabase.get(flow).then(function(database) {
            $scope.pipelines = $firebaseArray(
                database.ref().child('flows/'+flow.uuid+'/pipelines')
            );

            return $scope.pipelines.$loaded(function() {
                $scope.pipelines.$watch(function() {
                    watchPipelineTides(database);
                });

                return watchPipelineTides(database);
            });
        }).then(function() {
            $scope.isLoading = false;
        }));
    });
