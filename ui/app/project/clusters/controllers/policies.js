'use strict';

angular.module('continuousPipeRiver')
    .controller('ClusterPoliciesController', function($scope, $mdToast, $http, ClusterRepository, cluster) {
        $scope.isAdmin = true;
        $scope.cluster = cluster;

        $scope.availablePolicies = [
            {name: 'default'},
            {name: 'environment', configuration: {}, secrets: {}},
            {name: 'endpoint', configuration: {}, secrets: {}}
        ];

        $scope.addPolicy = function(policy) {
            cluster.policies.push(policy);
        };

        $scope.removePolicy = function(policyToRemove) {
            cluster.policies = cluster.policies.filter(function(clusterPolicy) {
                return clusterPolicy.name != policyToRemove.name;
            });
        };

        $scope.save = function() {
            $scope.isLoading = true;
            ClusterRepository.updatePolicies(cluster).then(function() {
                $mdToast.show($mdToast.simple()
                    .textContent('Policies successfully saved!')
                    .position('top')
                    .hideDelay(3000)
                    .parent($('md-content#content'))
                );
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occurred while updating policies", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    })
    .controller('EndpointClusterPolicyController', function($scope) {
        var refresh = function() {
            if ($scope.policy.configuration['cloudflare-by-default']) {
                $scope.policy.secrets = $.extend({
                    'cloudflare-zone-identifier': '',
                    'cloudflare-email': '',
                    'cloudflare-api-key': ''
                }, $scope.policy.secrets || {});
            }
        };

        $scope.onChange = function() {
            refresh();
        };

        refresh();
    });
