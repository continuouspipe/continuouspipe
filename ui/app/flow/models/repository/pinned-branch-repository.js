'use strict';

angular.module('continuousPipeRiver')
    .service('PinnedBranchRepository', function ($http, RIVER_API_URL, $mdToast) {
        this.pin = function (flowUuid, branch) {
            return $http.post(RIVER_API_URL + '/flows/' + flowUuid + '/pinned-branch',
                { name: branch }
            );
        };

        this.unpin = function (flowUuid, branch) {
            return $http({
                method: 'DELETE',
                url: RIVER_API_URL + '/flows/' + flowUuid + '/pinned-branch',
                data: { name: branch },
                headers: {
                    "Content-Type": "application/json"
                }
            });
        };
    });
