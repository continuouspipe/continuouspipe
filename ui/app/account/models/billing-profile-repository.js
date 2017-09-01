'use strict';

angular.module('continuousPipeRiver')
    .service('BillingProfileRepository', function($resource, AUTHENTICATOR_API_URL, RIVER_API_URL) {
        this.resource = $resource(AUTHENTICATOR_API_URL+'/api/billing-profile/:uuid');
        this.adminsResource = $resource(AUTHENTICATOR_API_URL+'/api/billing-profile/:uuid/admins/:username', {}, {
            delete: {
                method: 'DELETE'
            }
        });

        this.findMine = function() {
            return $resource(AUTHENTICATOR_API_URL+'/api/me/billing-profiles').query().$promise;
        };

        this.create = function(profile) {
            return $resource(AUTHENTICATOR_API_URL+'/api/me/billing-profiles').save(profile).$promise;
        };

        this.find = function(uuid) {
            return this.resource.get({uuid: uuid}).$promise;
        };

        this.delete = function(profile) {
            return this.resource.remove({uuid: profile.uuid}).$promise;
        };

        this.getUsage = function(profile, period) {
            return $resource(RIVER_API_URL+'/usage/aggregated').query({
                teams: profile.teams.map(function(team) {
                    return team.slug
                }).join(','),
                interval: period
            }).$promise;
        };

        this.addAdmin = function(billingProfile, username) {
            return this.adminsResource.save({
                uuid: billingProfile.uuid,
                username: username
            }, {}).$promise;
        };

        this.removeAdmin = function(billingProfile, username) {
            return this.adminsResource.delete({
                uuid: billingProfile.uuid,
                username: username
            }, {}).$promise;
        };

        this.findPlans = function() {
            return $resource(AUTHENTICATOR_API_URL+'/api/billing/plans').query().$promise;
        };

        this.changePlan = function(billingProfile, changeRequest) {
            return $resource(AUTHENTICATOR_API_URL+'/api/billing-profile/:uuid/change-plan').save({uuid: billingProfile.uuid}, changeRequest).$promise;
        };
    });
