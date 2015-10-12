'use strict';

angular.module('continuousPipeRiver')
    .service('TideRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/tides/:uuid');

        this.findByFlow = function(flow) {
            return $resource(RIVER_API_URL+'/flows/:uuid/tides').query({
                uuid: flow.uuid
            }).$promise;
        };

        this.find = function(uuid) {
            return this.resource.get({uuid: uuid}).$promise;
        };

        this.create = function(flow, tide) {
            return $resource(RIVER_API_URL+'/flows/:uuid/tides').save({
                uuid: flow.uuid
            }, tide).$promise;
        };
    })
    .service('TideSummaryRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/tides/:uuid/summary');

        this.findByTide = function(tide) {
            return this.resource.get({uuid: tide.uuid}).$promise;
        };
    });
