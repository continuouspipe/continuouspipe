'use strict';

angular.module('continuousPipeRiver')
    .config(function($httpProvider, jwtInterceptorProvider) {
        jwtInterceptorProvider.tokenGetter = ['$tokenStorage', function($tokenStorage) {
            return $tokenStorage.get();
        }];

        $httpProvider.interceptors.push(function($q, $authenticationProvider) {
            return {
                'responseError': function(response) {
                    if (response.status == 401) {
                        $authenticationProvider.handleAuthentication();
                    }

                    return $q.reject(response);
                }
            };
        });

        $httpProvider.interceptors.push('jwtInterceptor');
    })
    .run(function($authenticationProvider, $rootScope, $state, $errorContext) {
        if (!$authenticationProvider.isAuthenticated()) {
            $authenticationProvider.handleAuthentication();
        }

        $rootScope.$on('$stateChangeError', function(event, toState, toParams, fromState, fromParams, error) {
            $errorContext.set(error);

            // Do not retry or anything
            event.preventDefault();

            if (error.status == 401 || !$authenticationProvider.isAuthenticated()) {
                $authenticationProvider.handleAuthentication();
            } else if (error.status == 403) {
                $state.go('error.403', {}, {location: false});
            } else if (error.status == 404) {
                $state.go('error.404', {}, {location: false});
            } else {
                $state.go('error.500', {}, {location: false});                
            }
        });
    })
    .service('$errorContext', function() {
        this.get = function() {
            return this.error;
        };

        this.set = function(error) {
            this.error = error;
        };
    });
