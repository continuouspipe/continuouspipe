'use strict';

angular.module('continuousPipeRiver')
    .service('$authenticatedFirebaseDatabase', function($resource, $firebaseAuth, RIVER_API_URL) {
        var tokenResource = $resource(RIVER_API_URL+'/flows/:uuid/firebase-credentials');
        
        this.get = function(flow) {
            return tokenResource.get({uuid: flow.uuid}).$promise.then(function(credentials) {
                return $firebaseAuth().$signInWithCustomToken(credentials.token);
            }).then(function() {
                return firebase.database();
            });
        };
    });
