'use strict';

angular.module('continuousPipeRiver')
    .controller('ProjectConfigurationController', function($scope, $remoteResource, $http, $mdToast, ProjectRepository, UserRepository, project) {
        $scope.project = project;
        $scope.patch = {
            project: {
                name: project.name
            }
        };

        $scope.update = function() {
            if ($scope.patch.billing_profile) {
                swal({
                    title: "Are you sure?",
                    text: "You will change the billing profile of the project and will therefore be billed for its usage.",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, change it!",
                    closeOnConfirm: true
                }, function() {
                    doUpdate();
                });
            } else {
                doUpdate();
            }
        }

        var doUpdate = function() {
            $scope.isLoading = true;
            ProjectRepository.update(project, $scope.patch).then(function() {
                $mdToast.show($mdToast.simple()
                    .textContent('Configuration successfully saved!')
                    .position('top')
                    .hideDelay(3000)
                    .parent($('md-content.configuration-content'))
                );

                Intercom('trackEvent', 'updated-project', {
                    project: project
                });
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while create the project", "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };

        $scope.loadBillingProfiles = function() {
            return UserRepository.findBillingProfilesForCurrentUser().then(function(billingProfiles) {
                $scope.billingProfiles = billingProfiles;
            }, function(error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while loading your billing profiles", "error");
            });
        };
    });
