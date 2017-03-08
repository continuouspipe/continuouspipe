'use strict';

angular.module('continuousPipeRiver')
    .controller('ProjectsController', function ($scope, $remoteResource, ProjectRepository, $http) {
        $remoteResource.load('projects', ProjectRepository.findAll()).then(function (projects) {
            $scope.projects = projects;
        });

        $scope.delete = function (project) {
            $scope.isLoading = true;

            swal({
                title: "Are you sure?",
                text: "You will not be able to recover this project!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                closeOnConfirm: false
            }, function () {
                ProjectRepository.delete(project).then(function () {
                    swal("Deleted!", "project successfully deleted.", "success");
                }, function (error) {
                    swal("Error !", $http.getError(error) || "An unknown error occurred while deleting project", "error");
                })['finally'](function () {
                    $scope.isLoading = false;
                });
            });
        };
    })
    .controller('CreateProjectController', function ($scope, $state, $http, Slug, ProjectRepository) {
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
            }, function (error) {
                swal("Error !", $http.getError(error) || "An unknown error occured while create the project", "error");
            })['finally'](function () {
                $scope.isLoading = false;
            });
        };
    });
