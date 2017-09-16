'use strict';

angular.module('continuousPipeRiver')
    .controller('ProjectsController', function ($scope, $remoteResource, ProjectRepository, $http, $mdDialog) {
        $remoteResource.load('projects', ProjectRepository.findAll()).then(function (projects) {
            $scope.projects = projects;
        });

        $scope.create = function(event) {
            event.preventDefault();

            $mdDialog.show({
                controller: 'CreateProjectController',
                templateUrl: 'dashboard/views/projects/create.modal.html',
                parent: angular.element(document.body),
                targetEvent: event,
                clickOutsideToClose:true,
            });
        };
    })
    .controller('CreateProjectController', function ($scope, $state, $http, $mdDialog, Slug, ProjectRepository) {
        $scope.$watch('project.name', function (name, previous) {
            if ($scope.project && (!$scope.project.slug || $scope.project.slug == Slug.slugify(previous))) {
                $scope.project.slug = Slug.slugify(name);
            }
        });

        $scope.create = function (project) {
            $scope.isLoading = true;
            ProjectRepository.create(project).then(function () {
                $state.go('flows', { project: project.slug });

                Intercom('trackEvent', 'created-project', {
                    project: project
                });

                $mdDialog.hide();
            }, function (error) {
                $scope.hasErrored = true;
                swal("Error !", $http.getError(error) || "An unknown error occured while creating the project", "error");
            })['finally'](function () {
                $scope.isLoading = false;
            });
        };

        $scope.cancel = function() {
            $mdDialog.cancel();
        };
    });
