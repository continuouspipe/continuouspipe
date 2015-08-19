'use strict';

angular.module('continuousPipeRiver')
    .service('$remoteResource', function() {
        var resources = {};

        this.get = function(name) {
            if (!(name in resources)) {
                resources[name] = {
                    status: 'unknown'
                };
            }

            return resources[name];
        };

        this.load = function(name, promise) {
            var resource = this.get(name);
            resource.status = 'loading';

            promise.then(function() {
                resource.status = 'loaded';
            }, function(error) {
                resource.status = 'error';
                resource.error = 'An error appeared while loading '+name;
            });

            return promise;
        };

        this.remove = function(name) {
            delete resources[name];
        };
    });
