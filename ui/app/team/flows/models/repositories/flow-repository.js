'use strict';

angular.module('continuousPipeRiver')
    .service('FlowRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/flows/:uuid', {identifier: '@id'}, {
            update: {
                method: 'PUT'
            }
        });

        this.findByTeam = function(team) {
            return $resource(RIVER_API_URL+'/teams/:team/flows').query({team: team.slug}).$promise;
        };

        this.find = function(uuid) {
            return this.resource.get({uuid: uuid}).$promise;
        };

        this.remove = function(flow) {
            return this.resource.delete({uuid: flow.uuid}).$promise;
        };

        this.update = function(flow) {
            return this.resource.update({uuid: flow.uuid}, {
                yml_configuration: flow.yml_configuration
            }).$promise;
        };

        this.createFromRepository = function(repository) {
            return $resource(RIVER_API_URL+'/flows').save({
                repository: repository.repository.id,
                team: $teamContext.getCurrent().slug
            }).$promise;
        };
    });
