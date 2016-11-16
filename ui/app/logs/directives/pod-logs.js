angular.module('continuousPipeRiver')
    .directive('podLogs', function() {
        return {
            templateUrl: 'logs/views/pod/logs.html',
            scope: {
                environment: '=',
                pod: '='
            },
            controller: function($scope, $remoteResource, $http, $flowContext, LogFinder, RIVER_API_URL) {
                $scope.reload = function() {
                    $scope.timedOut = false;
                    $remoteResource.load('log', $http.post(RIVER_API_URL+'/flows/'+$flowContext.getCurrentFlow().uuid+'/environments/watch', {
                        'cluster': $scope.environment.cluster,
                        'environment': $scope.environment.identifier,
                        'pod': $scope.pod.name || $scope.pod.identifier
                    })).then(function(response) {
                        $scope.log = LogFinder.find(response.data.identifier);

                        var unwatch = $scope.log.$watch(function(event) {
                            $scope.timedOut = $scope.timedOut || $scope.log.timedOut;

                            if ($scope.log.status == 'finished') {
                                $scope.log.$destroy();

                                unwatch();
                            }
                        });
                    });
                };

                $scope.reload();
            }
        };
    });
