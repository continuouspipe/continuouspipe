'use strict';

angular.module('continuousPipeRiver')
    .service('FlowRepository', function($resource, RIVER_API_URL) {
        this.configurationResource = $resource(RIVER_API_URL+'/flows/:uuid/configuration', {identifier: '@id'}, {});
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

        this.createFromRepositoryAndTeam = function(team, repository) {
            return $resource(RIVER_API_URL+'/teams/:team/flows').save(
                {
                    team: team.slug
                },
                {
                    repository: repository.identifier
                }
            ).$promise;
        };

        this.getConfiguration = function(flow) {
            return this.configurationResource.get({uuid: flow.uuid}).$promise;
        };

        this.updateConfiguration = function(flow) {
            return this.configurationResource.save({uuid: flow.uuid}, {
                yml_configuration: flow.yml_configuration,
            }).$promise;
        };
    });
