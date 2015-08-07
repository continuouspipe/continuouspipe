'use strict';

angular.module('continuousPipeRiver')
    .service('FlowRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/flows/:uuid', {identifier: '@id'});

        this.findAll = function() {
            return this.resource.query().$promise;
        };

        this.find = function(uuid) {
            return this.resource.get({uuid: uuid}).$promise;
        };

        this.remove = function(flow) {
            return this.resource.delete({uuid: flow.uuid}).$promise;
        };

        this.createFromRepository = function(repository) {
            return $resource(RIVER_API_URL+'/flows/from-repository/:id', {identifier: '@id'}).save({id: repository.repository.id}, {}).$promise;
        };
    });
