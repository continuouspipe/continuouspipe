'use strict';

angular.module('continuousPipeRiver')
    .service('$authenticationProvider', function ($window, $tokenStorage, jwtHelper, RIVER_API_URL) {
        this.isAuthenticated = function() {
            if (!$tokenStorage.has()) {
                return false;
            }

            return !jwtHelper.isTokenExpired($tokenStorage.get());
        };

        this.handleAuthentication = function() {
            var location = $window.location.href;
            var tokenRegex = /[?&]?token=([^&]*)/g,
                token = tokenRegex.exec(location);

            if (token && token.length) {
                $tokenStorage.set(token[1]);
                $window.location.href = location.substring(0, token.index);
            } else {
                this.redirectToAuthentication();
            }
        };

        this.redirectToAuthentication = function() {
            $window.location.href = RIVER_API_URL+'/auth/authenticate?callback='+encodeURIComponent($window.location);
        };
    });
