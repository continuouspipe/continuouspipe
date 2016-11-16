'use strict';

angular.module('continuousPipeRiver')
    .service('$remoteResource', function($http) {
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
            var resource = this.get(name),
                resourceController = this;

            resource.status = 'loading';

            promise.then(function(result) {
                resource.status = 'loaded';

                // Handle the paginated lists
                if (result.pagination) {
                    resource.more = result.pagination.hasMore;
                    resource.loadMore = function() {
                        return resourceController.load(name, result.pagination.loadMore());
                    };
                }
            }, function(error) {
                resource.status = 'error';
                resource.error = $http.getError(error) || 'An error appeared while loading '+name;
            });

            return promise;
        };

        this.remove = function(name) {
            delete resources[name];
        };
    });
