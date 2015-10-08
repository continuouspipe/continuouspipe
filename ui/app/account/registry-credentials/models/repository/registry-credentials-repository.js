'use strict';

angular.module('continuousPipeRiver')
    .service('RegistryCredentialsRepository', function($resource, ACCOUNT_API_URL) {
        this.resource = $resource(ACCOUNT_API_URL+'/docker-registries/:serverAddress');

        this.findAll = function() {
            return this.resource.query().$promise;
        };

        this.remove = function(credentials) {
            return this.resource.delete({serverAddress: credentials.serverAddress}).$promise;
        };

        this.create = function(credentials) {
            return this.resource.save(credentials).$promise;
        };
    });
