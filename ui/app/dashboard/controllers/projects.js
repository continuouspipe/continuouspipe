'use strict';

angular.module('continuousPipeRiver')
    .controller('ProjectsController', function ($scope, $remoteResource, ProjectRepository) {
        $remoteResource.load('projects', ProjectRepository.findAll()).then(function (projects) {
            $scope.projects = projects;
        });
    })
    .service('ProjectCreationManager', function($q, $mdDialog, $state, BillingProfileRepository, ProjectRepository) {
        this.resolveBillingProfile = function(projectRequest) {
            if (projectRequest.$billing.type == 'new') {
                return BillingProfileRepository.create({
                    name: projectRequest.name
                });
            } else if (projectRequest.$billing.type == 'existing') {
                if (projectRequest.billing_profile) {
                    return $q.resolve(projectRequest.billing_profile);
                } else {
                    return $q.reject(new Error('No billing profile selected'));
                }
            } else {
                return $q.reject(new Error('Either create or re-use a billing profile'));
            }
        };

        this.changeBillingProfilePlanIfNeeded = function(projectRequest, billingProfile) {
            if (projectRequest.$billing.type != 'new') {
                return $q.resolve(projectRequest);
            }

            return BillingProfileRepository.changePlan(billingProfile, {
                plan: projectRequest.$billing.plan.identifier
            }).then(function(response) {
                if (!response.redirect_url) {
                    return $q.resolve();
                }

                return $mdDialog.show(
                    $mdDialog.alert()
                        .parent($('md-content#content'))
                        .clickOutsideToClose(false)
                        .title('You are going to be redirected.')
                        .textContent('In order to configure your billing details, you are going to be redirected. Please fill in your details and you\'ll be redirected to your newly created project.')
                        .ok('Got it!')
                ).then(function() {
                    var projectUrl = $state.href('flows', {
                        project: projectRequest.slug
                    }, {absolute: true});

                    window.location.href = response.redirect_url+
                        (response.redirect_url.indexOf('?') === -1 ? '?' : '&')+
                        'from='+projectUrl+
                        '&billing_profile='+billingProfile.uuid;
                });
            });
        };

        this.create = function(projectRequest) {
            return this.resolveBillingProfile(projectRequest).then(function(billingProfile) {
                return ProjectRepository.create(projectRequest, billingProfile).then(function(project) {
                    return this.changeBillingProfilePlanIfNeeded(projectRequest, billingProfile).then(function() {
                        return project;
                    }.bind(this));
                }.bind(this));
            }.bind(this));
        };
    })
    .controller('CreateProjectController', function ($scope, $state, $http, $remoteResource, Slug, ProjectCreationManager, ProjectRepository, BillingProfileRepository) {
        $scope.project = {};

        $scope.$watch('project.name', function (name, previous) {
            if ($scope.project && (!$scope.project.slug || $scope.project.slug == Slug.slugify(previous))) {
                $scope.project.slug = Slug.slugify(name);
            }
        });

        $scope.create = function (project) {
            $scope.isLoading = true;
            ProjectCreationManager.create(project).then(function () {
                Intercom('trackEvent', 'created-project', {
                    project: project
                });

                $state.go('flows', { project: project.slug });
            }, function (error) {
                $scope.hasErrored = true;
                swal("Error !", $http.getError(error) || "An unknown error occured while creating the project", "error");
            })['finally'](function () {
                $scope.isLoading = false;
            });
        };

        $remoteResource.load('plans', BillingProfileRepository.findPlans()).then(function(plans) {
            $scope.plans = plans;
        });

        var billingProfilesPromise = BillingProfileRepository.findMine();
        $remoteResource.load('billingProfiles', billingProfilesPromise.then(function(billingProfiles) {
            $scope.billingProfiles = billingProfiles;

            // Pre-load the existing vs new billing profile switch
            $scope.project.$billing = {
                type: billingProfiles.length == 0 ? 'new' : 'existing'
            };
        }));

        $scope.loadBillingProfiles = function() {
            return billingProfilesPromise;
        };
    });
