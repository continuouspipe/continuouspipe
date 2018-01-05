'use strict';

angular.module('continuousPipeRiver')
    .service('EnvironmentRepository', function($resource, $http, $q, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/flows/:uuid/environments/:name', {}, {
            delete: {
                method: 'DELETE'
            }
        });

        this.containersResource = $resource(RIVER_API_URL+'/flows/:uuid/clusters/:clusterIdentifier/namespaces/:namespace/pods/:podName', {}, {
            delete: {
                method: 'DELETE'
            }
        });

        this.findByFlow = function(flow) {
            return this.resource.query({uuid: flow.uuid}).$promise;
        };

        this.delete = function(flow, environment) {
            return this.resource.delete({
                uuid: flow.uuid, 
                name: environment.identifier,
                cluster: environment.cluster
            }).$promise;
        };

        this.deleteContainers = function(flow, environment, component) {
            return $q.all(component.status.containers.map(function(container) {
                return this.deleteContainer(flow, environment.cluster, environment.identifier, container.identifier);
            }.bind(this)))
        };

        this.deleteContainer = function(flow, cluster, namespace, pod) {
            return this.containersResource.delete({
                uuid: flow.uuid,
                clusterIdentifier: cluster,
                namespace: namespace,
                podName: pod
            });
        };
    });
