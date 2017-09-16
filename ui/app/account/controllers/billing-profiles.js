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
    .controller('ShowBillingProfileController', function($scope, $mdToast, $mdDialog, $http, $state, BillingProfileRepository, UsageGraphBuilder, billingProfile) {
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
            $scope.usage = usage;

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

        BillingProfileRepository.getUsage(billingProfile, 'P31D').then(function(usage) {
            if (usage.length) {
                $scope.billingProfile.plan.metrics.used = usage[0].entries[0].usage;
            }
        });

        $scope.change = function(ev) {
            var scope = $scope.$new();
            scope.billingProfile = billingProfile;

            $mdDialog.show({
                controller: 'ChangeBillingProfileController',
                templateUrl: 'account/views/billing-profiles/dialogs/change.html',
                targetEvent: ev,
                clickOutsideToClose:true,
                scope: scope

            }).then(function() {
                $state.reload();
            });
        };

        $scope.delete = function() {
            swal({
                title: "Are you sure?",
                text: "The billing profile won't be recoverable.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                closeOnConfirm: true
            }, function() {
                BillingProfileRepository.delete(billingProfile).then(function() {
                    $state.go('billing-profiles');
                }, function(error) {
                    swal("Error !", $http.getError(error) || "An unknown error occurred while deleting the billing profile", "error");
                });
            });
        };
    })
    .controller('ChangeBillingProfileController', function($scope, $http, $mdDialog, $mdToast, BillingProfileRepository) {
        var plansPromise = BillingProfileRepository.findPlans();
        $scope.changeRequest = {};

        $scope.loadPlans = function() {
            return plansPromise.then(function(plans) {
                $scope.plans = plans;
                return plans;
            });
        };

        $scope.close = function() {
            $mdDialog.cancel();
        };

        $scope.done = function() {
            $mdDialog.hide();
        };

        $scope.change = function(plan) {
            $scope.isLoading = true;
            BillingProfileRepository.changePlan($scope.billingProfile, {
                plan: $scope.changeRequest.plan.identifier
            }).then(function(response) {
                if (response.redirect_url) {
                    $scope.hasBeenRedirected = true;

                    window.location.href = response.redirect_url+
                        (response.redirect_url.indexOf('?') === -1 ? '?' : '&')+
                        'from='+window.location.href+
                        '&billing_profile='+$scope.billingProfile.uuid;
                } else {
                    $mdToast.show($mdToast.simple()
                        .textContent('Plan successfully changed')
                        .position('top')
                        .hideDelay(3000)
                        .parent($('md-content#content'))
                    );

                    $scope.done();
                }
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while changing plan", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };
    })
;
