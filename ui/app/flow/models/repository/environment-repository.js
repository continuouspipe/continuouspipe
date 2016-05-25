'use strict';

angular.module('continuousPipeRiver')
    .service('EnvironmentRepository', function($resource, $http, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/flows/:uuid/environments/:name', {}, {
            delete: {
                method: 'DELETE'
            }
        });

        this.findByFlow = function(flow) {
            return this.resource.query({uuid: flow.uuid}).$promise
        };

        this.delete = function(flow, environment) {
            return $http.delete(RIVER_API_URL+'/flows/'+flow.uuid+'/environments', {
                headers: {
                    'Content-Type': 'application/json;charset=utf-8'
                },
                data: {
                    identifier: environment.identifier,
                    cluster: environment.cluster
                }
            });
        };
    });
