'use strict';

angular.module('continuousPipeRiver')
    .service('AccountRepository', function($resource, RIVER_API_URL) {
        this.findMine = function() {
            return $resource(RIVER_API_URL+'/me/accounts').query().$promise;
        };

        this.findGoogleProjects = function(accountUuid) {
            return $resource(RIVER_API_URL+'/accounts/:uuid/google/projects').query({
                uuid: accountUuid
            }).$promise;
        };

        this.findGoogleClusters = function(accountUuid, projectId) {
            return $resource(RIVER_API_URL+'/accounts/:uuid/google/projects/:projectId/clusters').query({
                uuid: accountUuid,
                projectId: projectId
            }).$promise;
        };

        this.unlinkAccount = function(accountUuid) {
            return $resource(
                RIVER_API_URL+'/accounts/:uuid/unlink',
                {uuid:accountUuid},
                {unlink: {method:'POST'}}
            ).unlink().$promise;
        };

        this.connectAccount = function(type) {
            window.location.href = RIVER_API_URL+'/auth/connect/'+ type + '?redirectUrl=' + window.location.href;
        }
    });
