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
    })
    .service('$firebaseApplicationResolver', function($firebaseAuth) {
        var applications = {};

        this.init = function(name, configuration) {
            applications[name] = firebase.initializeApp(configuration, name);
        };

        this.get = function(descriptor) {
            if (!descriptor) {
                return Promise.resolve(firebase);
            }

            if (!descriptor.name) {
                throw new Error('Application "' + descriptor.name + '" has not be initialized before');
            } else if (!(descriptor.name in applications)) {
                throw new Error('Unable to figure out which application to use');
            }

            var application = applications[descriptor.name];
            var promise = Promise.resolve(application);

            if (descriptor.authentication_token) {
                promise = $firebaseAuth(application.auth()).$signInWithCustomToken(descriptor.authentication_token).then(function() {
                    return application;
                });
            }

            return promise;
        };
    })
    .service('$firebaseDatabaseResolver', function($firebaseApplicationResolver) {
        this.get = function(descriptor) {
            return $firebaseApplicationResolver.get(descriptor).then(function(application) {
                return application.database();
            });
        };
    });
