'use strict';

angular.module('continuousPipeRiver')
    .service('AlertsRepository', function($resource, $httpUtils, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/flows/:uuid/alerts');

        this.findByFlow = function(flow) {
            return $httpUtils.oneAtATime('alerts-flow-'+flow.uuid, function() {
                return this.resource.query({
                    uuid: flow.uuid
                }).$promise;
            }.bind(this));
        };
    });
