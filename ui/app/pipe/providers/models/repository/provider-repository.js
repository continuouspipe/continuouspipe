'use strict';

angular.module('continuousPipeRiver')
    .service('ProviderRepository', function($resource, PIPE_API_URL) {
        this.resource = $resource(PIPE_API_URL+'/providers/:uuid');

        this.findAll = function() {
            return this.resource.query().$promise;
        };

        this.remove = function(provider) {
            return this.resource.delete({uuid: provider.uuid}).$promise;
        };

        this.create = function(type, provider) {
            return $resource(PIPE_API_URL+'/providers/'+type).save(provider).$promise;
        };
    });
