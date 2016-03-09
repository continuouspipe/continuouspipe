'use strict';

angular.module('continuousPipeRiver')
    .directive('clusterSelector', function() {
        return {
            restrict: 'E',
            scope: {
                context: '='
            },
            templateUrl: 'account/clusters/views/directives/cluster-selector.html',
            controller: function($scope, $remoteResource, ClusterRepository) {
                $remoteResource.load('clusters', ClusterRepository.findAll()).then(function (clusters) {
                    $scope.clusters = clusters.map(function(cluster) {
                        return {
                            name: '('+cluster.type+') '+cluster.identifier,
                            identifier: cluster.identifier
                        };
                    });
                });
            }
        };
    });
