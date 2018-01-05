'use strict';

angular.module('continuousPipeRiver')
    .service('UsageNormalizer', function() {
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

        this.normalize = function(usage) {
            Object.keys(usage).forEach(function(key) {
                return usage[key] = normalizeAmount(usage[key]);
            });

            return usage;
        };
    })
    .service('UsageGraphBuilder', function(UsageNormalizer) {
        var groupEntriesBy = function(entries, groupByFunction) {
            var groupedEntries = {};

            entries.forEach(function(entry) {
                var key = groupByFunction(entry);

                // Parse entry's usage
                entry.usage = UsageNormalizer.normalize(entry.usage);

                if (!(key in groupedEntries)) {
                    groupedEntries[key] = entry;
                } else {
                    Object.keys(entry.usage).forEach(function(key) {
                        groupedEntries.usage[key] += entry.usage[key];
                    });
                }
            });

            return Object.values(groupedEntries);
        };

        this.dataFromUsage = function(usage, metric) {
            if (usage.length === 0) {
                return [];
            }
            
            var groupByFunction = function(entry) { return entry.flow.uuid },
                header = ['Date'];

            groupEntriesBy(usage[0].entries, groupByFunction).forEach(function(entry) {
                header.push(groupByFunction(entry));
            });

            var rows = [
                header
            ];

            usage.map(function(entry) {
                var leftDate = new Date(entry.datetime.left);
                var row = [
                    new Date(leftDate.getFullYear(), leftDate.getMonth(), leftDate.getDate())
                ];

                groupEntriesBy(entry.entries, groupByFunction).forEach(function(entry) {
                    row.push(entry.usage[metric]);
                });

                return row;
            }).forEach(function(row) {
                rows.push(row);
            });

            return rows;
        }
    });
