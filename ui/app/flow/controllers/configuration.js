'use strict';

angular.module('continuousPipeRiver')
    .controller('FlowConfigurationController', function($scope, flow) {
        $scope.availableTasks = [
            {name: 'build', description: 'Build Docker images found in your `docker-compose.yml` file.'},
            {name: 'run', description: 'Run a sequence of commands in a container.', context: {}},
            {name: 'deploy', description: 'Deploy the environment to a given Cloud Provider.', context: {}}
        ];

        $scope.addTask = function(index) {
            var configuration = $.extend(true, {}, $scope.availableTasks[index]);

            console.log('add configuration', configuration);
        };

        $scope.flow = flow;
        $scope.aceOption = {
            mode: 'yaml'
        };
    });
