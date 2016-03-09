'use strict';

angular.module('continuousPipeRiver')
    .service('$teamContext', function() {
        this.team = null;

        this.setCurrentTeam = function(team) {
            this.team = team;
        };

        this.getCurrentTeam = function() {
            return this.team;
        };
    });
