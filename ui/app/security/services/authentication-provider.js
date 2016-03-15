'use strict';

angular.module('continuousPipeRiver')
    .service('$authenticationProvider', function ($tokenStorage, jwtHelper, AUTHENTICATOR_API_URL) {
        this.isAuthenticated = function() {
            if (!$tokenStorage.has()) {
                return false;
            }

            return !jwtHelper.isTokenExpired($tokenStorage.get());
        };

        this.handleAuthentication = function() {
            var tokenRegex = /[?&]?token=([^&]*)/g,
                token = tokenRegex.exec(document.location);

            if (token && token.length) {
                $tokenStorage.set(token[1]);
            } else {
                this.redirectToAuthentication();
            }
        };

        this.redirectToAuthentication = function() {
            window.location.href = AUTHENTICATOR_API_URL+'/authenticate?callback='+encodeURIComponent(window.location);
        };
    });
