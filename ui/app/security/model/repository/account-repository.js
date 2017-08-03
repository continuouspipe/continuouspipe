'use strict';

angular.module('continuousPipeRiver')
    .service('AccountRepository', function($resource, AUTHENTICATOR_API_URL) {
        this.findMine = function() {
            return $resource(AUTHENTICATOR_API_URL+'/api/me/accounts').query().$promise;
        };

        this.findGoogleProjects = function(accountUuid) {
            return $resource(AUTHENTICATOR_API_URL+'/api/accounts/:uuid/google/projects').query({
                uuid: accountUuid
            }).$promise;
        };

        this.findGoogleClusters = function(accountUuid, projectId) {
            return $resource(AUTHENTICATOR_API_URL+'/api/accounts/:uuid/google/projects/:projectId/clusters').query({
                uuid: accountUuid,
                projectId: projectId
            }).$promise;
        };

        this.unlinkAccount = function(accountUuid) {
            return $resource(
                AUTHENTICATOR_API_URL+'/api/accounts/:uuid/unlink',
                {uuid:accountUuid},
                {unlink: {method:'POST'}}
            ).unlink().$promise;
        };

        this.connectAccount = function(type) {
            window.location.href = AUTHENTICATOR_API_URL+'/connect/'+ type + '?redirectUrl=' + window.location.href;
        }
    });
