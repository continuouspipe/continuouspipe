'use strict';

angular.module('continuousPipeRiver')
    .service('$teamContext', function(TeamRepository, $q) {
        this.teams = {};
        this.currentTeamSlug = localStorage.getItem('currentTeamSlug') || null;

        this.getAll = function() {
            return this.teams;
        };

        this.getCurrent = function() {
            if (null === this.currentTeamSlug) {
                this.currentTeamSlug = Object.keys(this.teams)[0];
            }

            return this.teams[this.currentTeamSlug];
        };

        this.setCurrentSlug = function(currentSlug) {
            this.currentTeamSlug = currentSlug;
            localStorage.setItem('currentTeamSlug', currentSlug);
        };

        this.refreshTeams = function() {
            var teamContext = this;

            return TeamRepository.findAll().then(function(teams) {
                teamContext.teams = {};
                for (var i = 0; i < teams.length; i++) {
                    var team = teams[i];

                    teamContext.teams[team.slug] = team;
                }

                return teams;
            }, function(error) {
                return $q.reject(error);
            });
        };
    });
