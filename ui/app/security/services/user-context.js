'use strict';

angular.module('continuousPipeRiver')
    .service('$userContext', function(jwtHelper, $tokenStorage) {
        this.getUser = function() {
            return jwtHelper.decodeToken($tokenStorage.get());
        };
    });
