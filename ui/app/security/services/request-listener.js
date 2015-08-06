'use strict';

angular.module('continuousPipeRiver')
    .config(function($httpProvider, jwtInterceptorProvider) {
        jwtInterceptorProvider.tokenGetter = ['$tokenStorage', function($tokenStorage) {
            return $tokenStorage.get();
        }];

        $httpProvider.interceptors.push('jwtInterceptor');
    })
    .run(function($authenticationProvider) {
        if (!$authenticationProvider.isAuthenticated()) {
            $authenticationProvider.handleAuthentication();
        }
    });
