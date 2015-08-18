'use strict';

angular.module('continuousPipeRiver')
    .service('OrganisationRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/user-organisations');

        this.findAll = function() {
            return this.resource.query().$promise;
        };
    });
