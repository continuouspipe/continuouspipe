'use strict';

angular.module('continuousPipeRiver')
    .config(function($httpProvider, jwtInterceptorProvider) {
        jwtInterceptorProvider.tokenGetter = ['$tokenStorage', function($tokenStorage) {
            return $tokenStorage.get();
        }];

        $httpProvider.interceptors.push('jwtInterceptor');
        $httpProvider.interceptors.push(function($q, $authenticationProvider, $injector) {
            return {
                'responseError': function(response) {
                    if (response.status == 401) {
                        $authenticationProvider.handleAuthentication();

                        return response;
                    }

                    return $q.reject(response);
                }
            };
        });
    })
    .run(function($authenticationProvider, $rootScope, $state, $errorContext) {
        if (!$authenticationProvider.isAuthenticated()) {
            $authenticationProvider.handleAuthentication();
        }

        $rootScope.$on('$stateChangeError', function(event, toState, toParams, fromState, fromParams, error) {
            $errorContext.set(error);

            if (error.status == 403) {
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
