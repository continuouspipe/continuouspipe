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
    .directive('podShell', function($tokenStorage, $window, KUBE_PROXY_HOSTNAME) {
        return {
            templateUrl: 'logs/views/pod/shell.html',
            scope: {
                environment: '=',
                pod: '='
            },
            controller: function($scope) {
                var term = new Terminal();
                term.open(document.getElementById('terminal-container'), true);

                $scope.connect = function() {
                    $scope.hasStarted = true;
                    $scope.isConnected = true;

                    var proxyWebSocketUri =
                        'wss://'+KUBE_PROXY_HOSTNAME+
                        '/'+$scope.environment.flow.uuid+'/'+$scope.environment.cluster+
                        '/api/v1'+
                        '/namespaces/'+$scope.environment.identifier+
                        '/pods/'+$scope.pod.identifier+
                        '/exec'+
                        '?'+
                        'stdout=1&stdin=1&stderr=1&tty=true'+
                        '&access_token='+$tokenStorage.get()
                    ;

                    ['/bin/bash', '-c', 'export TERM=xterm; exec bash'].forEach(function(command) {
                        proxyWebSocketUri += '&command='+encodeURIComponent(command);
                    });

                    var socket = new WebSocket(proxyWebSocketUri, 'base64.channel.k8s.io');

                    term.clear();
                    term.setOption('cursorBlink', true);

                    setTimeout(function() {
                        term.fit();
                    }, 500);

                    angular.element($window).bind('resize', function(){
                        term.fit();
                    });

                    socket.addEventListener('message', function(event) {
                        term.fit();
                        
                        var message = event.data,
                            contents = atob(message.substr(1));

                        term.write(contents);
                    });

                    term.on('data', function(data) {
                        socket.send('0'+btoa(data));
                    });

                    socket.addEventListener('error', function(error) {
                        console.log('error', error);
                        $scope.isConnected = false;
                    });

                    socket.addEventListener('close', function() {
                        console.log('Closed!');
                        $scope.isConnected = false;
                    });

                    $scope.$on('$destroy', function() {
                        socket.close();
                    });
                };
            }
        };
    });
