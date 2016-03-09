'use strict';

angular.module('continuousPipeRiver')
    .service('ClusterRepository', function($resource, $teamContext, AUTHENTICATOR_API_URL) {
        this.resource = $resource(AUTHENTICATOR_API_URL+'/api/bucket/:bucket/clusters/:identifier');

        var getBucketUuid = function() {
            return $teamContext.getCurrent().bucket_uuid;
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
    });
