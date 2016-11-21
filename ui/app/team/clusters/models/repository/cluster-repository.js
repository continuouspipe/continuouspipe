'use strict';

angular.module('continuousPipeRiver')
    .service('ClusterRepository', function($resource, $teamContext, AUTHENTICATOR_API_URL, RIVER_API_URL) {
        this.resource = $resource(AUTHENTICATOR_API_URL+'/api/bucket/:bucket/clusters/:identifier');

        var getBucketUuid = function() {
            return $teamContext.getCurrentTeam().bucket_uuid;
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

        this.findProblems = function(team, cluster) {
            return $resource(RIVER_API_URL+'/teams/:team/clusters/:cluster/health').query({
                team: team.slug,
                cluster: cluster.identifier
            }).$promise;
        };
    });
