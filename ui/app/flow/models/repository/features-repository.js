'use strict';

angular.module('continuousPipeRiver')
    .service('FeaturesRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/flows/:uuid/features/:feature');

        this.findAll = function(flow) {
            return this.resource.query({
                uuid: flow.uuid
            }).$promise;
        };

        this.enable = function(flow, feature) {
            return this.resource.save({uuid: flow.uuid, feature: feature}, {}).$promise;
        };
    });
