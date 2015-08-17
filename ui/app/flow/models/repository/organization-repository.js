'use strict';

angular.module('continuousPipeRiver')
    .service('OrganizationRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/user-organizations');

        this.findAll = function() {
            return this.resource.query().$promise;
        };
    });
