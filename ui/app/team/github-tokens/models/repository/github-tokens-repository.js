'use strict';

angular.module('continuousPipeRiver')
    .service('GitHubTokensRepository', function($resource, $teamContext, AUTHENTICATOR_API_URL) {
        this.resource = $resource(AUTHENTICATOR_API_URL+'/api/bucket/:bucket/github-tokens/:identifier');

        var getBucketUuid = function() {
            return $teamContext.getCurrentTeam().bucket_uuid;
        };

        this.findAll = function() {
            return this.resource.query({bucket: getBucketUuid()}).$promise;
        };

        this.remove = function(token) {
            return this.resource.delete({bucket: getBucketUuid(), identifier: token.identifier}).$promise;
        };

        this.create = function(token) {
            return this.resource.save({bucket: getBucketUuid()}, token).$promise;
        };
    });
