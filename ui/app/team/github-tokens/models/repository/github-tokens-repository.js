'use strict';

angular.module('continuousPipeRiver')
    .service('GitHubTokensRepository', function($resource, $teamContext, AUTHENTICATOR_API_URL) {
        this.resource = $resource(AUTHENTICATOR_API_URL+'/api/bucket/:bucket/github-tokens/:login');

        var getBucketUuid = function() {
            return $teamContext.getCurrent().bucket_uuid;
        };

        this.findAll = function() {
            return this.resource.query({bucket: getBucketUuid()}).$promise;
        };

        this.remove = function(token) {
            return this.resource.delete({bucket: getBucketUuid(), login: token.login}).$promise;
        };

        this.create = function(token) {
            return this.resource.save({bucket: getBucketUuid()}, token).$promise;
        };
    });
