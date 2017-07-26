'use strict';

function sortedTides (branch) {
    var lastTides = Object.values(branch.data['latest-tides']);
    lastTides.sort(function (left, right) {
        return left.creation_date > right.creation_date ? -1 : 1;
    });

    return lastTides;
}

function latestTideInBranch(branch) {
    if (!branch.data['latest-tides']) {
        return;
    }

    var lastTides = sortedTides(branch);

    return lastTides.length ? lastTides[0] : null;
}
angular.module('continuousPipeRiver')
    .filter('latestTideInBranch', function () {
        return latestTideInBranch;
    })
    .filter('branchLastTides', function() {
        return function (branch) {
            if (!branch.data['latest-tides']) {
                return [];
            }

            return sortedTides(branch).slice(0, 5);
        }
    })
    .filter('branchesWithoutPullRequest', function() {
        return function(branches) {
            return (branches || []).filter(function(branch) {
                return !branch.pull_request;
            })
        }
    })
    .filter('branchLatestCommit', function() {
        return function(branch) {
            if (!branch.data) {
                return;
            }

            if (branch.data['latest-commit']) {
                return branch.data['latest-commit'].sha;
            }

            var lastTide = latestTideInBranch(branch);
            if (!lastTide) {
                return;
            }

            return lastTide.code_reference.sha1;
        };
    })
;