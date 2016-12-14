'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowPipelinesController', function($scope, $remoteResource, flow, $firebaseArray, $authenticatedFirebaseDatabase) {
        $scope.flow = flow;

        $remoteResource.load('tides', $authenticatedFirebaseDatabase.get(flow).then(function(database) {
            $scope.tides = $firebaseArray(
                database.ref().child('flows/'+flow.uuid+'/tides').orderByChild('creation_date').limitToLast(20)
            );
        }));
    });
