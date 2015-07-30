angular.module('logstream')
    .directive('logTimeCounter', ['$interval', function($interval) {
        return {
            scope: {
                logTimeCounter: '='
            },
            restrict: 'A',
            template: '{{ timeInDate | date:\'HH:mm:ss\'}}',
            controller: ['$scope', function($scope) {
                var timeInterval = null;

                $scope.$watchGroup(['logTimeCounter.updatedAt', 'logTimeCounter.createdAt'], function() {
                    var until = $scope.logTimeCounter.updatedAt,
                        from = $scope.logTimeCounter.createdAt;

                    if (!until) {
                        timeInterval = $interval(function () {
                            updateTimeCounter(until, from);
                        });
                    } else if (timeInterval !== null) {
                        $interval.cancel(timeInterval);
                    }
                });

                function updateTimeCounter(until, from) {
                    if (!until) {
                        until = new Date();
                    } else if (!(until instanceof Date)) {
                        until = new Date(until);
                    }

                    if (!(from instanceof Date)) {
                        from = new Date(from);
                    }

                    $scope.timeInSeconds = Math.round((until - from) / 1000);
                    $scope.timeInDate = new Date(0, 0, 0, 0, 0, 0, 0).setSeconds($scope.timeInSeconds);
                }
            }]
        };
    }]);
