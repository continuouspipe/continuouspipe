'use strict';

angular.module('continuousPipeRiver')
    .controller('ProjectAddClusterController', function($scope, $state, $http, ClusterRepository, AccountRepository, AUTHENTICATOR_API_URL) {
        var clusterFromGkeCluster = function(gkeCluster) {
            return {
                type: 'kubernetes',
                identifier: gkeCluster.name,
                address: 'https://' + gkeCluster.endpoint,
                username: gkeCluster.masterAuth.username,
                password: gkeCluster.masterAuth.password,
                version: 'v' + gkeCluster.currentMasterVersion
            };
        };

        $scope.create = function(cluster) {
            var source = 'manual';
            if (cluster.type == 'gke') {
                cluster = clusterFromGkeCluster(cluster.gke.cluster);
                source = 'gke';
            }

            $scope.isLoading = true;

            ClusterRepository.create(cluster).then(function() {
                $state.go('clusters');

                Intercom('trackEvent', 'added-cluster', {
                    cluster: cluster,
                    source: source
                });
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while creating cluster", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };

        $scope.connectAccountUrl = AUTHENTICATOR_API_URL + '/account/connected-accounts';
        $scope.loadGoogleAccounts = function() {
            return AccountRepository.findMine().then(function(accounts) {
                $scope.googleAccounts = accounts.filter(function(account) {
                    return account.type == 'google';
                });
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while loading your Google accounts", "error");
            });
        };

        $scope.loadGoogleProjects = function(account) {
            return AccountRepository.findGoogleProjects(account.uuid).then(function(projects) {
                $scope.googleProjects = projects;
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while loading your Google projects", "error");
            });
        };

        $scope.loadGoogleCluster = function(account, project) {
            return AccountRepository.findGoogleClusters(account.uuid, project.projectId).then(function(clusters) {
                $scope.googleClusters = clusters;
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while loading your GKE clusters", "error");
            });
        };
    });
