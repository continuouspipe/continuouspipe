'use strict';

angular.module('continuousPipeRiver')
    .service('$userContext', function(jwtHelper, UserRepository, $tokenStorage, $q) {
        this.user = null;
        this.getUser = function() {
            return this.user;
        };

        this.refreshUser = function() {
            var decoded = jwtHelper.decodeToken($tokenStorage.get()),
                userContext = this;

            return UserRepository.findByUsername(decoded.username).then(function(user) {
                userContext.user = user;

                return user;
            }, function(error) {
                return $q.reject(error);
            });
        };
    });
