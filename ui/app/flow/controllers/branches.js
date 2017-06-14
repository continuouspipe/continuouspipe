'use strict';

angular.module('continuousPipeRiver')
    .controller('BranchesController', function ($scope, $http, $mdToast, $firebaseArray, $authenticatedFirebaseDatabase, PinnedBranchRepository, flow) {
        $authenticatedFirebaseDatabase.get(flow).then(function (database) {
            $scope.branches = $firebaseArray(
                database.ref()
                    .child('flows/' + flow.uuid + '/branches')
            );
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

        $scope.pullRequests = [
            {
                'identifier': '56',
                'title': 'My new feature',
                'branch': {
                    'name': 'feature/shiny-new-feature',
                    'pinned': false,
                    'last_tide': {
                        'status': 'success',
                        'code_reference' : {
                            'branch': 'my-new-feature',
                            'sha1': 'kjhad87287321'
                        },
                        'uuid': 'c7as98sidhqwd6a43434',
                        'creation_date': '03/05/2017 09:13:11',
                        'tasks': [
                            {
                                'status': 'successful',
                                'label': 'images'
                            },
                            {
                                'status': 'successful',
                                'label': 'deploy'
                            }
                        ]
                    },
                    'more_tides': [
                        {
                            'status': 'cancelled',
                            'code_reference' : {
                                'branch': 'master',
                                'sha1': '89876asgda6d4as'
                            },
                            'uuid': 'p98oakbduyats',
                            'creation_date': '03/05/2017 09:10:09',
                            'tasks': [
                                {
                                    'status': 'successful',
                                    'label': 'images'
                                },
                                {
                                    'status': 'cancelled',
                                    'label': 'deploy'
                                }
                            ]
                        },
                        {
                            'status': 'failed',
                            'code_reference' : {
                                'branch': 'master',
                                'sha1': 'aksjdai8sdoiahbsdy6'
                            },
                            'uuid': 'ksaj7isyiausd',
                            'creation_date': '03/05/2017 09:10:09',
                            'tasks': [
                                {
                                    'status': 'successful',
                                    'label': 'images'
                                },
                                {
                                    'status': 'failed',
                                    'label': 'deploy'
                                }
                            ]
                        }
                    ]
                }
            }
        ]
    });
