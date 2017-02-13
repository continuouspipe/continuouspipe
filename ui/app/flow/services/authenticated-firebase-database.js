'use strict';

angular.module('continuousPipeRiver')
    .service('$authenticatedFirebaseDatabase', function ($resource, $firebaseAuth, RIVER_API_URL) {
        var self = this;
        var tokenResource = $resource(RIVER_API_URL + '/flows/:uuid/firebase-credentials');

        this.setFlow = function (flow) {
            self.flow = flow;
        };

        this.setFireBaseCredentials = function (credentials) {
            localStorage.setItem('firebaseCredentials', {
                'token': credentials.token,
                'expiration_date': credentials.expiration_date
            });
        };

        this.checkTokenExpiration = function () {
            var sessionStart = localStorage.getItem('firebaseCredentials').expiration_date;
            var sessionLength = ((new Date(sessionStart).getTime() - new Date().getTime()) / 1000) / 60;

            if (59 > Math.abs(sessionLength)) {
                setTimeout(function () {
                    self.checkTokenExpiration();
                }, 10000);
            } else {
                self.get(self.flow);
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
