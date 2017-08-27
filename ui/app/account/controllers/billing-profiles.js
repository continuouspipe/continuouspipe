'use strict';

angular.module('continuousPipeRiver')
    .controller('BillingProfilesController', function ($scope, $remoteResource, BillingProfileRepository, $mdDialog, $state, $http) {
        $remoteResource.load('billingProfiles', BillingProfileRepository.findMine()).then(function(billingProfiles) {
            $scope.billingProfiles = billingProfiles;
        });

        $scope.create = function(ev) {
            var confirm = $mdDialog.prompt()
                .title('How would you like to name this new billing profile?')
                .placeholder('My company')
                .ariaLabel('Name')
                .targetEvent(ev)
                .ok('Create')
                .cancel('Cancel');

            $mdDialog.show(confirm).then(function(name) {
                $scope.isLoading = true;
                BillingProfileRepository.create({
                    name: name
                }).then(function (billingProfile) {
                    $state.go('billing-profile', {uuid: billingProfile.uuid});

                    Intercom('trackEvent', 'created-billing-profile', {
                        billingProfile: billingProfile
                    });
                }, function (error) {
                    swal("Error !", $http.getError(error) || "An unknown error occured while creating the billing profile", "error");
                })['finally'](function () {
                    $scope.isLoading = false;
                });
            });
        };
    })
    .controller('ShowBillingProfileController', function($scope, $mdToast, $http, $state, BillingProfileRepository, UsageGraphBuilder, billingProfile) {
        $scope.billingProfile = billingProfile;

        $scope.addAdmin = function(username) {
            $scope.isLoading = true;
            BillingProfileRepository.addAdmin(billingProfile, username).then(function() {
                $state.reload();

                $mdToast.show($mdToast.simple()
                    .textContent(username+' successfully added as an administrator')
                    .position('top')
                    .hideDelay(3000)
                    .parent($('md-content#content'))
                );
            }, function (error) {
                swal("Error !", $http.getError(error) || "An unknown error occurred while adding "+username+" as adminstrator", "error");
            })['finally'](function () {
                $scope.isLoading = false;
            });
        };

        $scope.removeAdmin = function(admin) {
            $scope.isLoading = true;
            BillingProfileRepository.removeAdmin(billingProfile, admin.username).then(function() {
                $state.reload();

                $mdToast.show($mdToast.simple()
                    .textContent(username+' successfully removed from the administrators')
                    .position('top')
                    .hideDelay(3000)
                    .parent($('md-content#content'))
                );
            }, function (error) {
                swal("Error !", $http.getError(error) || "An unknown error occurred while removing "+username+" from the administrators", "error");
            })['finally'](function () {
                $scope.isLoading = false;
            });
        };

        BillingProfileRepository.getUsage(billingProfile).then(function(usage) {
            $scope.tidesGraph = {
                type: 'SteppedAreaChart',
                data: UsageGraphBuilder.dataFromUsage(usage, 'tides'),
                options: {
                    isStacked: true,
                    chartArea: {width: '90%', height: '80%'},
                    hAxis: {
                        format: 'd/M/yy',
                        gridlines: {count: 15}
                    }
                }
            };

            $scope.resourcesGraph = {
                type: 'SteppedAreaChart',
                data: UsageGraphBuilder.dataFromUsage(usage, 'memory'),
                options: {
                    isStacked: true,
                    chartArea: {width: '90%', height: '80%'},
                    hAxis: {
                        format: 'd/M/yy',
                        gridlines: {count: 15}
                    }
                }
            };
        });
    });
