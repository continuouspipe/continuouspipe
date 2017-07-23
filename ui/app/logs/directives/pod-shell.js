angular.module('continuousPipeRiver')
    .directive('podSelector', function() {
        return {
            restrict: 'E',
            templateUrl: 'logs/views/dialogs/component-selector.html',
            controller: 'LogsComponentDialogController',
            transclude: true,
            scope: {
                title: '@',
                component: '='
            }
        };
    })
    .directive('podShell', function() {
        return {
            templateUrl: 'logs/views/pod/shell.html',
            scope: {
                environment: '=',
                pod: '='
            },
            controller: function($scope) {
                $scope.connect = function() {
                    $scope.hasStarted = true;
                    $scope.isLoading = true;

                    term = new Terminal();
                    term.on('data', function (data) {
                        console.log('send', data);
                        //socket.send(data);
                    });
                    term.open(document.getElementById('terminal-container'), true);
                    term.setOption('cursorBlink', true);
                    term.write("Connecting to container "+$scope.pod.identifier+"...");

                    setTimeout(function() {
                        term.fit();
                    }, 10);
                    //term.resize(width, height);

                    /**
                    socket.onmessage = function (e) {
                        term.write(e.data);
                    };
                    socket.onerror = function (error) {
                        $scope.state.connected = false;
                    };
                    socket.onclose = function(evt) {
                        $scope.state.connected = false;
                    };
                    **/
                };
            }
        };
    });
