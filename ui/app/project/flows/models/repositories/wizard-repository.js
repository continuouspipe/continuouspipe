'use strict';

angular.module('continuousPipeRiver')
    .service('WizardRepository', function($resource, $q, RIVER_API_URL) {
        this.findOrganisations = function(account) {
            return $resource(RIVER_API_URL+'/account/:uuid/organisations').query({uuid: account.uuid}).$promise;
        };

        this.findRepositoryByCurrentUser = function(account) {
            return cancellableQuery(RIVER_API_URL+'/account/:uuid/repositories', {uuid: account.uuid});
        };

        this.findRepositoryByOrganisation = function(account, organisation) {
            return cancellableQuery(RIVER_API_URL+'/account/:uuid/organisations/:identifier/repositories', {
                uuid: account.uuid,
                identifier: organisation.identifier
            });
        };

        var cancellableQuery = function(url, parameters) {
            var query = $resource(url, {}, {}, {cancellable: true}).query(parameters),
                promise = query.$promise;

            promise.cancel = function() {
                query.$cancelRequest();
            };

            return promise;
        }
    });
