'use strict';

angular.module('continuousPipeRiver')
    .service('UsageGraphBuilder', function() {

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

        this.dataFromUsage = function(usage, metric) {
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
                    row.push(entry.usage[metric]);
                });

                return row;
            }).forEach(function(row) {
                rows.push(row);
            });

            return rows;
        }
    });
