'use strict';

angular.module('continuousPipeRiver')
    .service('RepositoryRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/user-repositories');

        this.findForCurrentUser = function() {
            return this.resource.query().$promise;
        };

        this.findByOrganization = function(organization) {
            return $resource(RIVER_API_URL+'/user-repositories/organization/:organization')
                .query({organization: organization}).$promise;
        }
    });
