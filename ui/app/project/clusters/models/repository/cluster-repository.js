'use strict';

angular.module('continuousPipeRiver')
    .service('ClusterRepository', function($resource, $projectContext, $q, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/bucket/:bucket/clusters/:identifier', null, {
            update: { method: 'PATCH' }
        });

        var getBucketUuid = function(project) {
            if (!project) {
                project = $projectContext.getCurrentProject();
            }

            return project.bucket_uuid;
        };

        this.findAll = function(project) {
            return this.resource.query({bucket: getBucketUuid(project)}).$promise;
        };

        this.find = function(identifier, project) {
            return this.findAll(project).then(function(clusters) {
                for (var i = 0; i < clusters.length; i++) {
                    if (clusters[i].identifier == identifier) {
                        return clusters[i];
                    }
                }

                return $q.reject(new Error('Cluster not found'));
            });
        };

        this.updatePolicies = function(cluster) {
            return this.resource.update({bucket: getBucketUuid(), identifier: cluster.identifier}, {
                policies: cluster.policies
            }).$promise;
        };

        this.remove = function(cluster) {
            return this.resource.delete({bucket: getBucketUuid(), identifier: cluster.identifier}).$promise;
        };

        this.create = function(cluster) {
            // Normalize the cluster credentials
            if (cluster && cluster.credentials) {
                if (cluster.credentials.type == 'username_password') {
                    cluster.credentials = {type: cluster.credentials.type, username: cluster.credentials.username, password: cluster.credentials.password};
                } else if (cluster.credentials.type == 'client_certificate') {
                    cluster.credentials = {type: cluster.credentials.type, client_certificate: cluster.credentials.client_certificate, client_certificate_password: cluster.credentials.client_certificate_password};
                }
            }

            return this.resource.save({bucket: getBucketUuid()}, cluster).$promise;
        };

        this.createManaged = function(project) {
            return $resource(RIVER_API_URL+'/teams/:slug/managed/create-cluster', {
                slug: (project || $projectContext.getCurrentProject()).slug
            }).save().$promise;  
        };

        this.findProblems = function(project, cluster) {
            return $resource(RIVER_API_URL+'/teams/:project/clusters/:cluster/health').query({
                project: project.slug,
                cluster: cluster.identifier
            }).$promise;
        };
    });
