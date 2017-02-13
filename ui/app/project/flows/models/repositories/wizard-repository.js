'use strict';

angular.module('continuousPipeRiver')
    .service('WizardRepository', function($resource, RIVER_API_URL) {
        this.findOrganisations = function(account) {
            return $resource(RIVER_API_URL+'/account/:uuid/organisations').query({uuid: account.uuid}).$promise;
        };

        this.findRepositoryByCurrentUser = function(account) {
            return $resource(RIVER_API_URL+'/account/:uuid/repositories').query({uuid: account.uuid}).$promise;
        };

        this.findRepositoryByOrganisation = function(account, organisation) {
            return $resource(RIVER_API_URL+'/account/:uuid/organisations/:identifier/repositories').query({
                uuid: account.uuid,
                identifier: organisation.identifier
            }).$promise;
        };
    });
