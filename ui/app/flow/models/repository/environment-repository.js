'use strict';

angular.module('continuousPipeRiver')
    .service('EnvironmentRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/flows/:uuid/environments/:name', {}, {
            remove: {
                method: 'DELETE'
            }
        });

        this.findByFlow = function(flow) {
            return this.resource.query({uuid: flow.uuid}).$promise
        };

        this.delete = function(flow, environment) {
            return this.resource.remove({uuid: flow.uuid}, {
                identifier: environment.identifier,
                cluster: environment.cluster
            }).$promise;
        };
    });
