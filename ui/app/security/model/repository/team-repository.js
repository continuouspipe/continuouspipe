'use strict';

angular.module('continuousPipeRiver')
    .service('TeamRepository', function($resource, AUTHENTICATOR_API_URL) {
        this.resource = $resource(AUTHENTICATOR_API_URL+'/api/teams/:slug');

        this.findAll = function() {
            return this.resource.query().$promise
        };

        this.create = function(team) {
            return this.resource.save(team).$promise;
        };
    });
