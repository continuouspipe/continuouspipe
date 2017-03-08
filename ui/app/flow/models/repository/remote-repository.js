'use strict';

angular.module('continuousPipeRiver')
    .service('RemoteRepository', function (RIVER_API_URL, $resource) {
        this.resource = $resource(RIVER_API_URL + '/flows/:flowUuid/development-environments/:environmentUuid', {}, {
            delete: {
                method: 'DELETE'
            }
        });
        // https://github.com/continuouspipe/river/pull/331#issue-210540884

        this.getStatus = function(flow, uuid) {
            return $resource(RIVER_API_URL + '/flows/:flowUuid/development-environments/:environmentUuid/status')
                .get({flowUuid: flow.uuid, environmentUuid: uuid}).$promise;
        };

        this.findByFlow = function (flow) {
            return this.resource.query({flowUuid: flow.uuid}).$promise;
        };

        this.create = function (flow, environment) {
            return this.resource.save({flowUuid: flow.uuid}, environment).$promise;
        };

        this.delete = function (flow, environment) {
            return this.resource.delete({flowUuid: flow.uuid, environmentUuid: environment.uuid}).$promise;
        };

        this.issueToken = function (flow, environment, tokenRequest) {
            return $resource(RIVER_API_URL + '/flows/:flowUuid/development-environments/:environmentUuid/initialization-token').save({
                flowUuid: flow.uuid,
                environmentUuid: environment.uuid
            }, tokenRequest).$promise;
        };
    })
;
