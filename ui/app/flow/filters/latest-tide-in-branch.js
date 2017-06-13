'use strict';

angular.module('continuousPipeRiver')
    .filter('latestTideInBranch', function () {
        return function (branch) {
            if (!branch['latest-tides']) {
                return;
            }

            var lastTides = Object.values(branch['latest-tides']);
            lastTides.sort(function (left, right) {
                return left.creation_date < right.creation_date ? -1 : 1;
            });

            return lastTides.length ? lastTides[0] : null;
        };
    });
