'use strict';

angular.module('continuousPipeRiver')
    .service('RemoteRepository', function (RIVER_API_URL, $resource) {
        this.resource = $resource(RIVER_API_URL + '/flows/:uuid/development-environments');

        this.getDevEnvironments = function (flow) {
            return this.resource.query({uuid: flow.uuid}).$promise;
        };
    })
;
