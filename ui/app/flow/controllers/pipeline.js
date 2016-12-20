'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowPipelineController', function($scope, $remoteResource, flow, pipeline, $firebaseArray, $authenticatedFirebaseDatabase) {
        $scope.flow = flow;

        $remoteResource.load('tides', $authenticatedFirebaseDatabase.get(flow).then(function(database) {
            $scope.tides = $firebaseArray(
                database.ref().child('flows/'+flow.uuid+'/tides/by-pipelines/'+pipeline.uuid).orderByChild('creation_date').limitToLast(20)
            );
        }));
    });
