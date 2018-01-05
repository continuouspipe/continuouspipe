'use strict';

angular.module('continuousPipeRiver')
    .service('$tokenStorage', function () {
        this.has = function () {
            return this.get() !== null;
        };

        this.get = function() {
            return localStorage.getItem('token');
        };

        this.set = function(token) {
            localStorage.setItem('token', token);
        };

        this.remove = function() {
            localStorage.removeItem('token');
        }
    });
