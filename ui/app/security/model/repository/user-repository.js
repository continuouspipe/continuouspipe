'use strict';

angular.module('continuousPipeRiver')
    .service('UserRepository', function($resource, AUTHENTICATOR_API_URL) {
        this.resource = $resource(AUTHENTICATOR_API_URL+'/api/user/:username');
        this.apiKeyResource = $resource(AUTHENTICATOR_API_URL+'/api/user/:username/api-keys/:key');

        this.findByUsername = function(username) {
            return this.resource.get({username: username}).$promise.then(function(user) {
                user.isAdmin = function(project){
                    var matches = project.memberships.filter(function(member) {return member.user.username == user.username;});

                    return (matches.length > 0 ? matches[0].permissions : []).indexOf('ADMIN') > -1;
                };
                return user;
            })
        };

        this.findBillingProfilesForCurrentUser = function() {
            return $resource(AUTHENTICATOR_API_URL+'/api/me/billing-profiles').query().$promise;
        };

        this.findApiKeysByUsername = function(username) {
            return this.apiKeyResource.query({username: username}).$promise;
        };

        this.createApiKey = function(username, apiKey) {
            return this.apiKeyResource.save({username: username}, apiKey).$promise;
        };

        this.deleteApiKey = function(username, apiKey) {
            return this.apiKeyResource.delete({username: username, key: apiKey.api_key}).$promise;
        }
    });
