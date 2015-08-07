'use strict';

angular.module('continuousPipeRiver')
    .service('RepositoryRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/user-repositories/:id', {identifier: '@id'});

        this.findAll = function() {
            return this.resource.query().$promise;
        };
    });
