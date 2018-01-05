'use strict';

angular.module('continuousPipeRiver')
    .controller('ClusterPoliciesController', function($scope, $mdToast, $http, $userContext, ClusterRepository, cluster) {
        $scope.isAdmin = $userContext.isAdmin();
        $scope.cluster = cluster;

        $scope.availablePolicies = [
            {name: 'default'},
            {name: 'environment', configuration: {}, secrets: {}},
            {name: 'endpoint', configuration: {}, secrets: {}},
            {name: 'resources', configuration: {}, secrets: {}},
            {name: 'rbac', configuration: {}},
            {name: 'network', configuration: {rules: []}}
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

            if ($scope.policy.configuration['ssl-certificate-defaults']) {
                $scope.policy.secrets = $.extend({
                    'ssl-certificate-key': '',
                    'ssl-certificate-cert': ''
                }, $scope.policy.secrets || {});
            }
        };

        $scope.deleteHostRuleByIndex = function(index) {
             $scope.policy.configuration['host-rules'].splice(index, 1);
        };

        $scope.addHostRule = function() {
            if (!$scope.policy.configuration['host-rules']) {
                $scope.policy.configuration['host-rules'] = [];
            }
            
            $scope.policy.configuration['host-rules'].push({});
        }

        $scope.onChange = function() {
            refresh();
        };

        refresh();
    })
    .controller('NetworkClusterPolicyController', function($scope) {
        $scope.deleteNetworkRuleByIndex = function(index) {
             $scope.policy.configuration['rules'].splice(index, 1);
        };

        $scope.addNetworkRule = function() {
            if (!$scope.policy.configuration['rules']) {
                $scope.policy.configuration['rules'] = [];
            }
            
            $scope.policy.configuration['rules'].push({});
        };
    });
