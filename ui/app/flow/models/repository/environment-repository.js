'use strict';

angular.module('continuousPipeRiver')
    .service('EnvironmentRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/flows/:uuid/environments/:name');

        this.findByFlow = function(flow) {
            return this.resource.query({uuid: flow.uuid}).$promise
        };
    });
