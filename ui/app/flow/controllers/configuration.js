'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowConfigurationController', function($scope, $timeout, FlowRepository, flow) {
        $scope.availableTasks = [
            {name: 'build', description: 'Build Docker images found in your `docker-compose.yml` file.'},
            {name: 'run', description: 'Run a sequence of commands in a container.', context: {}},
            {name: 'deploy', description: 'Deploy the environment to a given Cloud Provider.', context: {}}
        ];

        $scope.addTask = function(index) {
            var task = $.extend(true, {}, $scope.availableTasks[index]),
                taskName = task.name,
                taskConfiguration = task.context;

            var configuration = jsyaml.safeLoad($scope.flow.yml_configuration);
            if (!configuration.tasks) {
                configuration.tasks = {};
            }

            var taskIndex = Object.keys(configuration.tasks).length;
            configuration.tasks[taskName+taskIndex] = {};
            configuration.tasks[taskName+taskIndex][taskName] = taskConfiguration || null;

            $scope.flow.yml_configuration = jsyaml.safeDump(configuration, {
                indent: 4
            });
        };

        $scope.update = function() {
            $scope.isLoading = true;

            FlowRepository.update(flow).then(function() {
                $scope.successMessage = 'Saved !';
                $timeout(function() {
                    $scope.successMessage = null;
                }, 2000);
            }, function(error) {
                var message = ((error || {}).data || {}).message || "An unknown error occured while creating flow";
                swal("Error !", message, "error");
            })['finally'](function() {
                $scope.isLoading = false;
            });
        };

        $scope.flow = flow;
        $scope.aceOption = {
            mode: 'yaml'
        };
    });
