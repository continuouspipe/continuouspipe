'use strict';

angular.module('continuousPipeRiver')
    .service('UserRepository', function($resource, AUTHENTICATOR_API_URL) {
        this.resource = $resource(AUTHENTICATOR_API_URL+'/api/user/:username');

        this.findByUsername = function(username) {
            return this.resource.get({username: username}).$promise
        };

        this.findBillingProfilesForCurrentUser = function() {
            return $resource(AUTHENTICATOR_API_URL+'/api/me/billing-profiles').query().$promise;
        };
    });
