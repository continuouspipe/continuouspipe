'use strict';

angular.module('continuousPipeRiver')
    .controller('BranchesController', function ($scope, $http, $mdToast, $firebaseArray, $authenticatedFirebaseDatabase, PinnedBranchRepository, flow, user, project) {
        $scope.isAdmin = user.isAdmin(project);
        
        $authenticatedFirebaseDatabase.get(flow).then(function (database) {
            $scope.branches = $firebaseArray(
                database.ref().child('flows/' + flow.uuid + '/branches')
            );

            var pullRequestsByBranch = $firebaseArray(
                database.ref().child('flows/' + flow.uuid + '/pull-requests/by-branch')
            );


            var reloadPullRequests = function() {
                $scope.pullRequests = pullRequestsByBranch
                    .filter(function(pullRequestInBranch) {
                        return pullRequestInBranch.$id in $scope.branches;
                    })
                    .map(function(pullRequestInBranch) {
                        var view = $scope.branches[pullRequestInBranch.$id];
                        view.pull_request = $pullRequestInBranch;

                        return view;
                    })
                ;
            };

            $scope.branches.$loaded(reloadPullRequests);
            pullRequestsByBranch.$watch(reloadPullRequests);
        });

        $scope.pinOrUnPin = function(branch) {
            var method = branch.pinned ? 'unpin' : 'pin';
            PinnedBranchRepository[method](flow.uuid, branch.name).then(function(response) {
                $mdToast.show($mdToast.simple()
                    .textContent('Branch successfully '+method+'ned')
                    .position('top')
                    .hideDelay(3000)
                    .parent($('#content')));
            }, function(response) {
                swal("Error !", $http.getError(response), "error");
            })
        };
    });
