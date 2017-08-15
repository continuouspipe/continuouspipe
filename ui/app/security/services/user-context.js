'use strict';

angular.module('continuousPipeRiver')
    .service('$userContext', function(jwtHelper, UserRepository, $tokenStorage, $q, $rootScope) {
        this.user = null;
        this.getUser = function() {
            return this.user;
        };

        this.refreshUser = function() {
            var decoded = jwtHelper.decodeToken($tokenStorage.get()),
                userContext = this;

            return UserRepository.findByUsername(decoded.username).then(function(user) {
                userContext.user = user;

                $rootScope.$emit('user_context.user_updated', user);

                return user;
            }, function(error) {
                return $q.reject(error);
            });
        };

        this.getRoles = function() {
            var decoded = jwtHelper.decodeToken($tokenStorage.get());

            return decoded.roles || [];
        };

        this.isAdmin = function() {
            return this.getRoles().indexOf('ROLE_ADMIN') !== -1;
        }
    });
