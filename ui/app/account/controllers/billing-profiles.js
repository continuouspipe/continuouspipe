'use strict';

angular.module('continuousPipeRiver')
    .controller('BillingProfilesController', function ($scope, $remoteResource, BillingProfileRepository, $mdDialog, $state, $http) {
        $remoteResource.load('billingProfiles', BillingProfileRepository.findMine()).then(function(billingProfiles) {
            $scope.billingProfiles = billingProfiles;
        });

        $scope.create = function(ev) {
            var confirm = $mdDialog.prompt()
                .title('How would you like to name this new billing profile?')
                .placeholder('My company')
                .ariaLabel('Name')
                .targetEvent(ev)
                .ok('Create')
                .cancel('Cancel');

            $mdDialog.show(confirm).then(function(name) {
                $scope.isLoading = true;
                BillingProfileRepository.create({
                    name: name
                }).then(function (billingProfile) {
                    $state.go('billing-profile', {uuid: billingProfile.uuid});

                    Intercom('trackEvent', 'created-billing-profile', {
                        billingProfile: billingProfile
                    });
                }, function (error) {
                    swal("Error !", $http.getError(error) || "An unknown error occured while creating the billing profile", "error");
                })['finally'](function () {
                    $scope.isLoading = false;
                });
            });
        };
    })
    .controller('ShowBillingProfileController', function($scope, BillingProfileRepository, billingProfile) {
        $scope.billingProfile = billingProfile;

        var normalizeAmount = function(amount) {
            if (typeof amount == 'string') {
                if (amount.substr(-2) == 'Gi') {
                    amount = parseFloat(amount.substr(0, amount.length - 2)) * 1000;
                } else if (amount.substr(-2) == 'Mi') {
                    amount = parseFloat(amount.substr(0, amount.length - 2));
                } else if (amount.substr(-1) == 'm') {
                    amount = parseFloat(amount.substr(0, amount.length - 1)) / 1000;
                }
            }

            return parseFloat(amount);
        };

        var normalizeUsage = function(usage) {
            Object.keys(usage).forEach(function(key) {
                return usage[key] = normalizeAmount(usage[key]);
            });

            return usage;
        };
        
        var groupEntriesBy = function(entries, groupByFunction) {
            var groupedEntries = {};

            entries.forEach(function(entry) {
                var key = groupByFunction(entry);

                // Parse entry's usage
                entry.usage = normalizeUsage(entry.usage);

                if (!key in groupedEntries) {
                    groupedEntries[key] = entry;
                } else {
                    Object.keys(entry.usage).forEach(function(key) {
                        groupedEntries.usage[key] += entry.usage[key];
                    });
                }
            });

            return Object.values(groupedEntries);
        };

        var graphDataFromUsage = function(usage, key) {
            var groupByFunction = function(entry) { return entry.flow.uuid },
                header = ['Date'];

            groupEntriesBy(usage[0].entries, groupByFunction).forEach(function(entry) {
                header.push(groupByFunction(entry));
            });

            var rows = [
                header
            ];

            usage.map(function(entry) {
                var row = [
                    (new Date(entry.datetime.left)).toDateString()
                ];

                groupEntriesBy(entry.entries, groupByFunction).forEach(function(entry) {
                    row.push(entry.usage[key]);
                });

                return row;
            }).forEach(function(row) {
                rows.push(row);
            });
            
            return rows;
        };

        BillingProfileRepository.getUsage(billingProfile).then(function(usage) {
            $scope.tidesGraph = {
                type: 'SteppedAreaChart',
                data: graphDataFromUsage(usage, 'tides'),
                options: {
                    title: 'Number of tides',
                    isStacked: true
                }
            };

            $scope.resourcesGraph = {
                type: 'SteppedAreaChart',
                data: graphDataFromUsage(usage, 'memory'),
                options: {
                    title: 'Resources (Memory)',
                    isStacked: true
                }
            };
        });
    });
