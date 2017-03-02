'use strict';

angular.module('continuousPipeRiver')
    .service('RemoteRepository', function (RIVER_API_URL, $resource) {
        var API = RIVER_API_URL + '/flows/:uuid/development-environments';
        // https://github.com/continuouspipe/river/pull/331#issue-210540884

        this.getDevEnvironments = function (flow) {
            return $resource(API).query({uuid: flow.uuid}).$promise;
        };

        this.issueToken = function (branchName, env, flow) {
            return $resource(RIVER_API_URL + '/flows/:uuid/development-environments/:envUuid/initialization-token', {}, {
                create: {
                    method: 'POST'
                }
            }).create({
                uuid: flow.uuid,
                envUuid: env.uuid
            }, {git_branch: branchName}).$promise;
        };

        this.createDevEnvironment = function (name, flow) {
            return $resource(API, {}, {
                create: {
                    method: 'POST'
                }
            }).create({
                uuid: flow.uuid
            }, {name: name}).$promise;
        };
    })
;
