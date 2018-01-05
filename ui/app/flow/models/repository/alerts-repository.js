'use strict';

angular.module('continuousPipeRiver')
    .service('AlertsRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/flows/:uuid/alerts');

        this.findByFlow = function(flow) {
            return this.resource.query({
                uuid: flow.uuid
            }).$promise;
        };
    });
