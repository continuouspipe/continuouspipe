angular.module('continuousPipeRiver')
	.service('LogFinder', function($firebaseObject) {
		firebase.initializeApp({
		    apiKey: "AIzaSyDIK_08syPHkRxcf2n8zJ48XAVPHWpTsp0",
		    authDomain: "continuous-pipe.firebaseapp.com",
		    databaseURL: "https://continuous-pipe.firebaseio.com",
		});

		this.find = function(identifier) {
			var root = firebase.database().ref().child('logs');

			return $firebaseObject(root.child(identifier));
		};
	});
