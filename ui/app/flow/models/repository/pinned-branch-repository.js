'use strict';

angular.module('continuousPipeRiver')
    .service('PinnedBranchRepository', function ($http, RIVER_API_URL, $mdToast) {
        this.pin = function (flowUuid, branch) {
            return $http({
                method: 'POST',
                url: RIVER_API_URL + '/flows/' + flowUuid + '/branch/' + encodeURIComponent(branch) + '/pinned'
            });
        };

        this.unpin = function (flowUuid, branch) {
            return $http({
                method: 'DELETE',
                url: RIVER_API_URL + '/flows/' + flowUuid + '/branch/' + encodeURIComponent(branch) + '/pinned'
            });
        };
    });
