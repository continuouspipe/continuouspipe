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
                        var log = LogFinder.find(response.data.identifier);

                        log.$bindTo($scope, 'log').then(function(unbind) {
                            var unwatch = $scope.$watch('log', function(log) {
                                if (!log) {
                                    return;
                                }

                                $scope.timedOut = $scope.timedOut || log.timedOut;

                                if (log.status == 'finished') {
                                    console.log('Unbind log');
                                    unbind();
                                    unwatch();
                                }
                            });
                        });
                    });
                };

                $scope.reload();
            }
        };
    });
