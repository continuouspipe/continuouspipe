'use strict';

angular.module('continuousPipeRiver')
    .service('BillingProfileRepository', function($resource, AUTHENTICATOR_API_URL, RIVER_API_URL) {
        this.resource = $resource(AUTHENTICATOR_API_URL+'/api/billing-profiles/:uuid');

        this.findMine = function() {
            return $resource(AUTHENTICATOR_API_URL+'/api/me/billing-profiles').query().$promise;
        };

        this.create = function(profile) {
            return $resource(AUTHENTICATOR_API_URL+'/api/me/billing-profiles').save(profile).$promise;
        };

        this.find = function(uuid) {
            return this.resource.get({uuid: uuid}).$promise;
        };

        this.delete = function(profile) {
            return this.resource.remove({uuid: profile.uuid}).$promise;
        };
    });
