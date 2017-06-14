'use strict';

angular.module('continuousPipeRiver')
    .service('PinnedBranchRepository', function ($http, RIVER_API_URL, $mdToast) {
        this.pin = function (flowUuid, branch) {
            return $http({
                method: 'PUT',
                url: RIVER_API_URL + '/flows/' + flowUuid + '/pinned-branch/' + encodeURIComponent(branch)
            });
        };

        this.unpin = function (flowUuid, branch) {
            return $http({
                method: 'DELETE',
                url: RIVER_API_URL + '/flows/' + flowUuid + '/pinned-branch/' + encodeURIComponent(branch)
            });
        };
    });
