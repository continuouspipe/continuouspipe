'use strict';

angular.module('continuousPipeRiver')
    .service('RegistryCredentialsRepository', function($resource, $projectContext, AUTHENTICATOR_API_URL) {
        this.resource = $resource(AUTHENTICATOR_API_URL+'/api/bucket/:bucket/docker-registries/:serverAddress');

        var getBucketUuid = function() {
            return $projectContext.getCurrentProject().bucket_uuid;
        };

        this.findAll = function() {
            return this.resource.query({bucket: getBucketUuid()}).$promise;
        };

        this.remove = function(credentials) {
            return this.resource.delete({bucket: getBucketUuid(), serverAddress: credentials.serverAddress}).$promise;
        };

        this.create = function(credentials) {
            return this.resource.save({bucket: getBucketUuid()}, credentials).$promise;
        };
    });
