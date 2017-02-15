'use strict';

angular.module('continuousPipeRiver')
    .service('ClusterRepository', function($resource, $projectContext, AUTHENTICATOR_API_URL, RIVER_API_URL) {
        this.resource = $resource(AUTHENTICATOR_API_URL+'/api/bucket/:bucket/clusters/:identifier');

        var getBucketUuid = function() {
            return $projectContext.getCurrentProject().bucket_uuid;
        };

        this.findAll = function() {
            return this.resource.query({bucket: getBucketUuid()}).$promise;
        };

        this.remove = function(cluster) {
            return this.resource.delete({bucket: getBucketUuid(), identifier: cluster.identifier}).$promise;
        };

        this.create = function(cluster) {
            return this.resource.save({bucket: getBucketUuid()}, cluster).$promise;
        };

        this.findProblems = function(project, cluster) {
            return $resource(RIVER_API_URL+'/teams/:project/clusters/:cluster/health').query({
                project: project.slug,
                cluster: cluster.identifier
            }).$promise;
        };
    });
