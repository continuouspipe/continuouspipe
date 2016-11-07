'use strict';

angular.module('continuousPipeRiver')
    .service('GoogleRepository', function($resource, AUTHENTICATOR_API_URL) {
        this.findMyAccounts = function() {
            return $resource(AUTHENTICATOR_API_URL+'/api/me/accounts').query().$promise;
        };

        this.findProjects = function(accountUuid) {
            return $resource(AUTHENTICATOR_API_URL+'/api/accounts/:uuid/google/projects').query({
            	uuid: accountUuid
            }).$promise;
        };

    	this.findClusters = function(accountUuid, projectId) {
			return $resource(AUTHENTICATOR_API_URL+'/api/accounts/:uuid/google/projects/:projectId/clusters').query({
            	uuid: accountUuid,
            	projectId: projectId
            }).$promise;
    	};
    });
