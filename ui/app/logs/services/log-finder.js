angular.module('continuousPipeRiver')
	.service('LogFinder', function($firebaseObject) {
		firebase.initializeApp({
		    apiKey: "AIzaSyDIK_08syPHkRxcf2n8zJ48XAVPHWpTsp0",
		    authDomain: "continuous-pipe.firebaseapp.com",
		    databaseURL: "https://continuous-pipe.firebaseio.com",
		});

		this.find = function(identifier) {
			return $firebaseObject(this.getReference(identifier));
		};

		this.getReference = function(identifier) {
			var root = firebase.database().ref();

			if (identifier.substr(0, 1) != '/') {
				root = root.child('logs');
			}

			return root.child(identifier);
		};
	});
