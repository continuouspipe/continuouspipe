'use strict';

angular.module('continuousPipeRiver')
    .directive('pipelineOverview', function ($authenticatedFirebaseDatabase, $firebaseArray) {
        return {
            restrict: 'E',
            scope: {
                pipeline: '=',
                flow: '=',
                disableDeletion: '@',
                headline: '@'
            },
            templateUrl: 'flow/views/directives/pipeline-overview.html',
            controller: function ($scope, PipelineRepository) {
                $authenticatedFirebaseDatabase.get($scope.flow).then(function (database) {
                    $scope.pipeline.lastTides = [];

                    var dbRef = database.ref()
                        .child('flows/' + $scope.flow.uuid + '/tides/by-pipelines/' + $scope.pipeline.uuid)
                        .orderByChild('creation_date')
                        .limitToLast(10);

                    dbRef.on('value', function (data) {
                        var environments = data.val();

                        Object.keys(environments)
                            .map(function (key) { return environments[key]; })
                            .filter(function (env) { return env.code_reference.branch === 'dev-briandgls'; })
                            .reduce(function (a, b) {
                                return new Date(a.creation_date) > new Date(b.creation_date) ? a : b;
                            });
                    });

                    $scope.pipeline.lastTides = $firebaseArray(dbRef);
                });

                $scope.deletePipeline = function (pipelineId) {
                    PipelineRepository.delete($scope.flow.uuid, pipelineId);
                };
            }
        };
    })
    ;
