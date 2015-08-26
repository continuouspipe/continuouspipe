'use strict';

angular.module('continuousPipeRiver')
    .service('ProviderRepository', function($resource, PIPE_API_URL) {
        this.resource = $resource(PIPE_API_URL+'/providers/:type/:identifier');

        this.findAll = function() {
            return this.resource.query().$promise;
        };

        this.remove = function(provider) {
            return this.resource.delete({type: provider.type, identifier: provider.identifier}).$promise;
        };

        this.create = function(type, provider) {
            return $resource(PIPE_API_URL+'/providers/'+type).save(provider).$promise;
        };
    });
