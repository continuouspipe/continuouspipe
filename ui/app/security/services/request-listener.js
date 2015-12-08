'use strict';

angular.module('continuousPipeRiver')
    .config(function($httpProvider, jwtInterceptorProvider) {
        jwtInterceptorProvider.tokenGetter = ['$tokenStorage', function($tokenStorage) {
            return $tokenStorage.get();
        }];

        $httpProvider.interceptors.push('jwtInterceptor');

        $httpProvider.interceptors.push(function($q, $authenticationProvider) {
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
    .run(function($authenticationProvider) {
        if (!$authenticationProvider.isAuthenticated()) {
            $authenticationProvider.handleAuthentication();
        }
    });
