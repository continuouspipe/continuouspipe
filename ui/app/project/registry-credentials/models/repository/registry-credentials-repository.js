'use strict';

angular.module('continuousPipeRiver')
    .service('RegistryCredentialsRepository', function($resource, $projectContext, AUTHENTICATOR_API_URL, RIVER_API_URL) {
        this.resource = $resource(AUTHENTICATOR_API_URL+'/api/bucket/:bucket/docker-registries/:serverAddress');

        var getBucketUuid = function(project) {
            return (project || $projectContext.getCurrentProject()).bucket_uuid;
        };

        this.findAll = function(project) {
            return this.resource.query({bucket: getBucketUuid(project)}).$promise;
        };

        this.remove = function(credentials) {
            return this.resource.delete({bucket: getBucketUuid(), serverAddress: credentials.serverAddress}).$promise;
        };

        this.create = function(credentials) {
            return this.resource.save({bucket: getBucketUuid()}, credentials).$promise;
        };

        this.createManagedForFlow = function(flow, visibility) {
            return $resource(RIVER_API_URL+'/flows/:uuid/resources/registry').save({uuid: flow.uuid}, {
                visibility: visibility
            }).$promise;
        };
    });
