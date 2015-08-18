'use strict';

angular.module('continuousPipeRiver')
    .service('RepositoryRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/user-repositories');

        this.findForCurrentUser = function() {
            return this.resource.query().$promise;
        };

        this.findByOrganisation = function(organisation) {
            return $resource(RIVER_API_URL+'/user-repositories/organisation/:organisation')
                .query({organisation: organisation}).$promise;
        }
    });
