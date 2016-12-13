'use strict';

angular.module('continuousPipeRiver')
    .service('WizardRepository', function($resource, RIVER_API_URL) {
        this.findOrganisations = function() {
            return $resource(RIVER_API_URL+'/wizard/organisations').query().$promise;
        };

        this.findRepositoryByCurrentUser = function() {
            return $resource(RIVER_API_URL+'/wizard/repositories').query().$promise;
        };

        this.findRepositoryByOrganisation = function(organisation) {
            return $resource(RIVER_API_URL+'/wizard/organisations/:login/repositories').query({
                login: organisation.organisation.login
            }).$promise;
        };

        this.findComponentsByRepositoryAndBranch = function(repository, branch) {
            return $resource(RIVER_API_URL+'/wizard/repositories/:id/components/:branch').query({
                id: repository.identifier,
                branch: branch
            }).$promise;
        };
    });
