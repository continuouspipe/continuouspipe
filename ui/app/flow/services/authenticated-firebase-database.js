'use strict';

angular.module('continuousPipeRiver')
    .service('$authenticatedFirebaseDatabase', function ($resource, $firebaseAuth, RIVER_API_URL) {
        var self = this;
        var tokenResource = $resource(RIVER_API_URL + '/flows/:uuid/firebase-credentials');

        this.setFlow = function (flow) {
            self.flow = flow;
        };

        this.setFireBaseCredentials = function (credentials) {
            localStorage.setItem('firebaseCredentials', JSON.stringify({
                'token': credentials.token,
                'expiration_date': credentials.expiration_date
            }));
        };

        this.checkTokenExpiration = function () {
            var sessionStart = JSON.parse(localStorage.getItem('firebaseCredentials')).expiration_date;
            var timeDiff = new Date(sessionStart).getTime() - new Date().getTime();
            var sessionExpired = Math.round(((timeDiff % 86400000) % 3600000) / 60000) <= 1;

            if (sessionExpired) {
                self.get(self.flow);
            } else {
                setTimeout(function () {
                    self.checkTokenExpiration();
                }, 10000);
            }
        };

        this.get = function (flow) {
            self.setFlow(flow);

            return tokenResource.get({ uuid: flow.uuid }).$promise.then(function (credentials) {
                self.setFireBaseCredentials(credentials);
                self.checkTokenExpiration();

                return $firebaseAuth().$signInWithCustomToken(credentials.token);
            }).then(function () {
                return firebase.database();
            });
        };
    });
