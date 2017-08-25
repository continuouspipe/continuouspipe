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
    });
