'use strict';

angular.module('continuousPipeRiver')
    .service('FlowRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/flows/:uuid', {identifier: '@id'});

        this.findAll = function() {
            return this.resource.query().$promise;
        };

        this.remove = function(flow) {
            return this.resource.delete({uuid: flow.uuid}).$promise;
        };
    });
