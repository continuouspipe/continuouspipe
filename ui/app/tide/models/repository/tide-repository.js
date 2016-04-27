'use strict';

angular.module('continuousPipeRiver')
    .service('TideRepository', function($resource, $q, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/tides/:uuid');

        this.buildListPagination = function(flow, list, limit, page) {
            return {
                hasMore: (list.loaded_length || list.length) >= limit,
                loadMore: function() {
                    return this.findByFlow(flow, limit, page + 1).then(function(itemsToAdd) {
                        itemsToAdd.forEach(function(item) {
                            list.push(item);
                        });

                        list.loaded_length = itemsToAdd.length;
                        list.pagination = this.buildListPagination(flow, list, limit, page + 1);

                        return list;
                    }.bind(this));
                }.bind(this)
            };
        };

        this.findByFlow = function(flow, limit, page) {
            limit = limit || 20;
            page = page || 1;

            return $resource(RIVER_API_URL+'/flows/:uuid/tides').query({
                uuid: flow.uuid,
                limit: limit,
                page: page
            }).$promise.then(function(list) {
                list.pagination = this.buildListPagination(flow, list, limit, page);

                return list;
            }.bind(this), function(error) {
                return $q.reject(error);
            });
        };

        this.find = function(uuid) {
            return this.resource.get({uuid: uuid}).$promise;
        };

        this.create = function(flow, tide) {
            return $resource(RIVER_API_URL+'/flows/:uuid/tides').save({
                uuid: flow.uuid
            }, tide).$promise;
        };

        this.cancel = function(tide) {
            return $resource(RIVER_API_URL+'/tides/:uuid/cancel').save({
                uuid: tide.uuid
            }, {}).$promise;
        };
    })
    .service('TideSummaryRepository', function($resource, RIVER_API_URL) {
        this.resource = $resource(RIVER_API_URL+'/tides/:uuid/summary');

        this.findByTide = function(tide) {
            return this.resource.get({uuid: tide.uuid}).$promise;
        };

        this.findExternalRelations = function(tide) {
            return $resource(RIVER_API_URL+'/tides/:uuid/external-relations').query({
                uuid: tide.uuid
            }).$promise;
        };
    });
