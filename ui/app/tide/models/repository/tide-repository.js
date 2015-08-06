'use strict';

angular.module('continuousPipeRiver')
    .service('TideRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/tides/:uuid', {identifier: '@id'});

        this.findByFlow = function(flow) {
            return $resource(RIVER_API_URL+'/flows/:uuid/tides', {identifier: '@id'}).query({
                uuid: flow.uuid
            }).$promise;
        };

        this.find = function(uuid) {
            return this.resource.get({uuid: uuid}).$promise;
        };
    });
